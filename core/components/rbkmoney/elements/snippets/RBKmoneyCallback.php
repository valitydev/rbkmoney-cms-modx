<?php

use src\Api\Customers\CustomerResponse\Status as CustomerStatus;
use src\Api\Exceptions\WrongDataException;
use src\Api\Exceptions\WrongRequestException;
use src\Api\Payments\CreatePayment\HoldType;
use src\Api\Payments\CreatePayment\Request\CreatePaymentRequest;
use src\Api\Payments\CreatePayment\Request\CustomerPayerRequest;
use src\Api\Payments\CreatePayment\Request\PaymentFlowHoldRequest;
use src\Api\Payments\CreatePayment\Request\PaymentFlowInstantRequest;
use src\Api\Webhooks\InvoicesTopicScope;
use src\Client\Client;
use src\Client\Sender;
use src\Exceptions\RequestException;
use src\Helpers\Log;
use src\Helpers\Logger;

$callback = new RBKmoneyCallback();

$callback->handle();

class RBKmoneyCallback
{
    /**
     * @var Sender
     */
    private $sender;

    /**
     * @var array
     */
    private $settings = [];

    /**
     * @var modX
     */
    private $modx;

    /**
     * @var string
     */
    private $corePath;

    function __construct()
    {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/config.core.php';
        require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
        require_once MODX_CONNECTORS_PATH . 'index.php';

        $this->modx = $modx;
        $this->corePath = $this->modx->getOption(
            'rbkmoney_core_path',
            null,
            $this->modx->getOption('core_path') . 'components/rbkmoney/'
        );

        $lang = 'ru';
        if ($this->modx->getOption('manager_language') != $lang) {
            $lang = 'en';
        }

        require_once $this->corePath . "lang/settings.$lang.php";
        require_once $this->corePath . 'src/settings.php';
        require_once $this->corePath . 'src/autoload.php';

        $this->loadRBKmoneyClasses();

        $settings = $modx->getCollection(RBK_MONEY_SETTINGS_CLASS);

        /**
         * @var $setting RBKmoneySettings
         */
        foreach ($settings as $setting) {
            $this->settings[$setting->get('code')] = $setting->get('value');
        }
        $callbackPath = $this->modx->makeUrl($this->settings['callbackPageId']);
        $this->settings['callbackUrl'] = "http://{$_SERVER['HTTP_HOST']}/$callbackPath";

        $this->sender = new Sender(
            new Client(
                $this->modx,
                $this->settings['apiKey'],
                $this->settings['shopId'],
                RBK_MONEY_API_URL_SETTING
            )
        );
    }

    /**
     * @return void
     */
    private function loadRBKmoneyClasses()
    {
        $dbClassPath = $this->corePath . 'model/rbkmoney/';

        $this->modx->loadClass(RBK_MONEY_SETTINGS_CLASS, $dbClassPath);
        $this->modx->loadClass(RBK_MONEY_RECURRENT_CUSTOMERS_CLASS, $dbClassPath);
        $this->modx->loadClass(RBK_MONEY_RECURRENT_ITEMS_CLASS, $dbClassPath);
        $this->modx->loadClass(RBK_MONEY_RECURRENT_CLASS, $dbClassPath);
        $this->modx->loadClass(RBK_MONEY_INVOICE_CLASS, $dbClassPath);
    }

    /**
     * @return void
     */
    public function handle()
    {
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/test.txt', file_get_contents('php://input'));
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/head.txt', getenv('HTTP_CONTENT_SIGNATURE'));
        try {
            $signature = $this->getSignatureFromHeader(getenv('HTTP_CONTENT_SIGNATURE'));

            if (empty($signature)) {
                throw new WrongDataException(RBK_MONEY_WRONG_SIGNATURE, HTTP_CODE_FORBIDDEN);
            }

            $signDecode = base64_decode(strtr($signature, '-_,', '+/='));

            $message = file_get_contents('php://input');

            if (empty($message)) {
                throw new WrongDataException(RBK_MONEY_WRONG_VALUE . ' `callback`', HTTP_CODE_BAD_REQUEST);
            }

            if (!$this->verificationSignature($message, $signDecode)) {
                throw new WrongDataException(RBK_MONEY_WRONG_SIGNATURE, HTTP_CODE_FORBIDDEN);
            }

            $callback = json_decode($message);

            if (isset($callback->invoice)) {
                $this->paymentCallback($callback);
            } elseif (isset($callback->customer)) {
                $this->customerCallback($callback->customer);
            }
        } catch (Exception $exception) {
            $this->callbackError($exception);
        }

        if (RBK_MONEY_SHOW_PARAMETER === $this->settings['saveLogs']) {
            if (!empty($exception)) {
                $responseMessage = $exception->getMessage();
                $responseCode = $exception->getCode();
            } else {
                $responseMessage = '';
                $responseCode = HTTP_CODE_OK;
            }

            $log = new Log(
                $this->settings['callbackUrl'],
                'POST',
                json_encode(getallheaders()),
                $responseMessage,
                'Content-Type: application/json'
            );

            $log->setRequestBody(file_get_contents('php://input'))
                ->setResponseCode($responseCode);

            $logger = new Logger();
            $logger->saveLog($log);
        }
    }

    /**
     * @param string $data
     * @param string $signature
     *
     * @return bool
     */
    function verificationSignature($data, $signature)
    {
        $publicKeyId = openssl_pkey_get_public($this->settings['publicKey']);

        if (empty($publicKeyId)) {
            return false;
        }

        $verify = openssl_verify($data, $signature, $publicKeyId, OPENSSL_ALGO_SHA256);

        return ($verify == 1);
    }

    /**
     * Возвращает сигнатуру из хедера для верификации
     *
     * @param string $contentSignature
     *
     * @return string
     *
     * @throws WrongDataException
     */
    private function getSignatureFromHeader($contentSignature)
    {
        $signature = preg_replace("/alg=(\S+);\sdigest=/", '', $contentSignature);

        if (empty($signature)) {
            throw new WrongDataException(RBK_MONEY_WRONG_SIGNATURE, HTTP_CODE_FORBIDDEN);
        }

        return $signature;
    }

    /**
     * @param Exception $exception
     */
    private function callbackError(Exception $exception)
    {
        header('Content-Type: application/json', true, $exception->getCode());

        echo json_encode(['message' => $exception->getMessage()], 256);
    }

    /**
     * @param stdClass $customer
     *
     * @return void
     *
     * @throws RequestException
     * @throws WrongDataException
     * @throws WrongRequestException
     */
    private function customerCallback(stdClass $customer)
    {
        $this->updateCustomerStatus($customer);

        if ($holdType = (RBK_MONEY_PAYMENT_TYPE_HOLD === $this->settings['paymentType'])) {
            $paymentFlow = new PaymentFlowHoldRequest($this->getHoldType());
        } else {
            $paymentFlow = new PaymentFlowInstantRequest();
        }

        $payRequest = new CreatePaymentRequest(
            $paymentFlow,
            new CustomerPayerRequest($customer->id),
            $customer->metadata->firstInvoiceId
        );

        $this->sender->sendCreatePaymentRequest($payRequest);
    }

    /**
     * @return HoldType
     *
     * @throws WrongDataException
     */
    private function getHoldType()
    {
        $holdType = (RBK_MONEY_EXPIRATION_PAYER === $this->settings['holdExpiration'])
            ? HoldType::CANCEL : HoldType::CAPTURE;

        return new HoldType($holdType);
    }

    /**
     * @param stdClass $customer
     *
     * @return void
     *
     * @throws WrongDataException
     */
    private function updateCustomerStatus(stdClass $customer)
    {
        $status = new CustomerStatus($customer->status);

        /**
         * @var $customer RBKmoneyRecurrentCustomers
         */
        $customer = $this->modx->getObject(
            RBK_MONEY_RECURRENT_CUSTOMERS_CLASS,
            ['user_id' => $customer->metadata->userId]
        );

        $customer->set('status', $status->getValue());
        $customer->save();
    }

    /**
     * @param stdClass $callback
     */
    private function paymentCallback(stdClass $callback)
    {
        if (isset($callback->invoice->metadata->orderId)) {

            /**
             * @var $order msOrder
             */
            $order = $this->modx->getObject(MS_ORDER_CLASS, [
                'id' => $callback->invoice->metadata->orderId
            ]);

            if (isset($callback->eventType)) {
                $type = $callback->eventType;
                $miniShop2 = $this->modx->getService('miniShop2');

                if (in_array($type, [
                    InvoicesTopicScope::INVOICE_PAID,
                    InvoicesTopicScope::PAYMENT_CAPTURED,
                ])) {
                    $paymentStatus = $this->modx->getObject(
                        MS_ORDER_STATUS_CLASS,
                        ['name' => $this->settings['successStatus']]
                    );

                    $miniShop2->changeOrderStatus($order->get('id'), $paymentStatus->get('id'));

                    include $this->corePath . 'src/Customers.php';

                    $customers = new Customers($this->sender, $this->modx);
                    $customers->setRecurrentReadyStatuses($order, $this->modx);
                } elseif (in_array($type, [
                    InvoicesTopicScope::INVOICE_CANCELLED,
                    InvoicesTopicScope::PAYMENT_CANCELLED,
                ])) {
                    $cancelStatus = $this->modx->getObject(
                        MS_ORDER_STATUS_CLASS,
                        ['name' => $this->settings['cancelStatus']]
                    );

                    $miniShop2->changeOrderStatus($order->get('id'), $cancelStatus->get('id'));
                } elseif (InvoicesTopicScope::PAYMENT_REFUNDED === $type) {
                    $refundStatus = $this->modx->getObject(
                        MS_ORDER_STATUS_CLASS,
                        ['name' => $this->settings['refundStatus']]
                    );

                    $miniShop2->changeOrderStatus($order->get('id'), $refundStatus->get('id'));
                } elseif (InvoicesTopicScope::PAYMENT_PROCESSED === $type) {
                    $holdStatus = $this->modx->getObject(
                        MS_ORDER_STATUS_CLASS,
                        ['name' => $this->settings['holdStatus']]
                    );

                    $miniShop2->changeOrderStatus($order->get('id'), $holdStatus->get('id'));
                }
            }
        }
    }

}

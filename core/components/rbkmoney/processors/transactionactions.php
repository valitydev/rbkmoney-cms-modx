<?php

use src\Api\Exceptions\WrongDataException;
use src\Api\Exceptions\WrongRequestException;
use src\Api\Payments\CancelPayment\Request\CancelPaymentRequest;
use src\Api\Payments\CapturePayment\Request\CapturePaymentRequest;
use src\Api\Payments\CreateRefund\Request\CreateRefundRequest;
use src\Client\Client;
use src\Client\Sender;
use src\Exceptions\RequestException;

require_once $_SERVER['DOCUMENT_ROOT'] . '/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

$method = $_POST['method'];
$invoiceId = $_POST['invoiceId'];
$paymentId = $_POST['paymentId'];

$actions = new TransactionActions($modx);

echo json_encode($actions->$method($invoiceId, $paymentId));

class TransactionActions
{

    /**
     * @var array
     */
    private $settings = [];

    /**
     * @var Sender
     */
    private $sender;

    /**
     * @param modX $modx
     */
    public function __construct(modX $modx)
    {
        $this->modx = $modx;
        $corePath = $this->modx->getOption('rbkmoney_core_path', null, $modx->getOption('core_path') . 'components/rbkmoney/');

        /**
         * @var $setting RBKmoneySettings
         */
        foreach ($this->modx->getCollection('RBKmoneySettings') as $setting) {
            $this->settings[$setting->get('code')] = $setting->get('value');
        }

        require_once $corePath . 'src/settings.php';
        require_once $corePath . 'src/autoload.php';

        $this->sender = new Sender(new Client(
            $modx,
            $this->settings['apiKey'],
            $this->settings['shopId'],
            RBK_MONEY_API_URL_SETTING
        ));
    }

    /**
     * @param string $invoiceId
     * @param string $paymentId
     *
     * @return array
     *
     * @throws WrongRequestException
     */
    public function confirmPayment($invoiceId, $paymentId)
    {
        $capturePayment = new CapturePaymentRequest(
            $invoiceId,
            $paymentId,
            RBK_MONEY_CAPTURED_BY_ADMIN
        );

        try {
            $this->sender->sendCapturePaymentRequest($capturePayment);

            $success = true;
            $message = RBK_MONEY_PAYMENT_CONFIRMED;
        } catch (RequestException $exception) {
            $success = false;
            $message = RBK_MONEY_PAYMENT_CAPTURE_ERROR;
        }

        return [
            'success' => $success,
            'message' => $message,
        ];
    }

    /**
     * @param string $invoiceId
     * @param string $paymentId
     *
     * @return array
     *
     * @throws WrongRequestException
     */
    public function cancelPayment($invoiceId, $paymentId)
    {
        $capturePayment = new CancelPaymentRequest(
            $invoiceId,
            $paymentId,
            RBK_MONEY_CAPTURED_BY_ADMIN
        );

        try {
            $this->sender->sendCancelPaymentRequest($capturePayment);

            $success = true;
            $message = RBK_MONEY_PAYMENT_CANCELLED;
        } catch (RequestException $exception) {
            $success = false;
            $message = RBK_MONEY_PAYMENT_CANCELLED_ERROR;
        }

        return [
            'success' => $success,
            'message' => $message,
        ];
    }

    /**
     * @param string $invoiceId
     * @param string $paymentId
     *
     * @return array
     *
     * @throws WrongDataException
     * @throws WrongRequestException
     */
    public function createRefund($invoiceId, $paymentId)
    {
        $capturePayment = new CreateRefundRequest(
            $invoiceId,
            $paymentId,
            RBK_MONEY_CAPTURED_BY_ADMIN
        );

        try {
            $this->sender->sendCreateRefundRequest($capturePayment);
            $success = true;
            $message = RBK_MONEY_REFUND_CREATED;
        } catch (RequestException $exception) {
            $success = false;
            $message = RBK_MONEY_REFUND_CREATE_ERROR;
        }

        return [
            'success' => $success,
            'message' => $message,
        ];
    }

}

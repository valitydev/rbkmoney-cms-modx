<?php

use src\Api\Exceptions\WrongDataException;
use src\Api\Exceptions\WrongRequestException;
use src\Api\Invoices\CreateInvoice\Cart;
use src\Api\Invoices\CreateInvoice\Request\CreateInvoiceRequest;
use src\Api\Invoices\CreateInvoice\Response\CreateInvoiceResponse;
use src\Api\Invoices\CreateInvoice\TaxMode;
use src\Api\Metadata;
use src\Api\Payments\CreatePayment\Request\CreatePaymentRequest;
use src\Api\Payments\CreatePayment\Request\CustomerPayerRequest;
use src\Api\Payments\CreatePayment\Request\PaymentFlowInstantRequest;
use src\Client\Client;
use src\Client\Sender;
use src\Exceptions\RequestException;

$recurrent = new RBKmoneyPaymentRecurrent($modx);

foreach ($recurrent->getRecurrentPayments() as $payment) {
    if (!empty($customer = $recurrent->getCustomer($payment->get('recurrent_customer_id')))) {
        $user = $recurrent->getUser($customer->get('user_id'));
    }

    try {
        $recurrent->createPayment(
            $recurrent->createInvoice($payment, $user),
            $customer->get('customer_id')
        );

        echo RBK_MONEY_RECURRENT_SUCCESS . $payment->id . PHP_EOL;;
    } catch (Exception $exception) {
        echo $exception->getMessage();
    }
}

class RBKmoneyPaymentRecurrent
{
    /**
     * @var array
     */
    private $settings;

    /**
     * @var modX
     */
    private $modx;

    /**
     * @var Sender
     */
    private $sender;

    /**
     * @param modX $modx
     */
    function __construct(modX $modx)
    {
        $this->modx = $modx;
        $corePath = $this->modx->getOption(
            'rbkmoney_core_path',
            null,
            $this->modx->getOption('core_path') . 'components/rbkmoney/'
        );

        $lang = $this->modx->getOption('manager_language');
        if (!file_exists($corePath . "lang/settings.$lang.php")) {
            $lang = 'en';
        }

        require_once $corePath . "lang/settings.$lang.php";
        require_once $corePath . 'src/settings.php';
        require_once $corePath . 'src/autoload.php';

        $this->loadRBKmoneyClasses($corePath);

        $settings = $modx->getCollection(RBK_MONEY_SETTINGS_CLASS);

        /**
         * @var $setting RBKmoneySettings
         */
        foreach ($settings as $setting) {
            $this->settings[$setting->get('code')] = $setting->get('value');
        }

        $callbackPath = $this->modx->makeUrl($this->settings['callbackPageId']);
        $this->settings['callbackUrl'] = "{$_SERVER['HTTP_HOST']}/$callbackPath";

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
     * @param string $corePath
     *
     * @return void
     */
    private function loadRBKmoneyClasses($corePath)
    {
        $dbClassPath = $corePath . 'model/rbkmoney/';

        $this->modx->loadClass(RBK_MONEY_SETTINGS_CLASS, $dbClassPath);
        $this->modx->loadClass(RBK_MONEY_RECURRENT_CUSTOMERS_CLASS, $dbClassPath);
        $this->modx->loadClass(RBK_MONEY_RECURRENT_ITEMS_CLASS, $dbClassPath);
        $this->modx->loadClass(RBK_MONEY_RECURRENT_CLASS, $dbClassPath);
        $this->modx->loadClass(RBK_MONEY_INVOICE_CLASS, $dbClassPath);
    }

    /**
     * @return array
     */
    public function getRecurrentPayments()
    {
        return $this->modx->getCollection(RBK_MONEY_RECURRENT_CLASS, ['status' => RECURRENT_READY_STATUS]);
    }

    /**
     * @param int $recurrentCustomerId
     *
     * @return object | null
     */
    public function getCustomer($recurrentCustomerId)
    {
        return $this->modx->getObject(RBK_MONEY_RECURRENT_CUSTOMERS_CLASS, ['id' => $recurrentCustomerId]);
    }

    /**
     * @param int $userId
     *
     * @return object | null
     */
    public function getUser($userId)
    {
        return $this->modx->getObject(MODX_USER_CLASS, ['id' => $userId]);
    }

    /**
     * @param RBKmoneyRecurrent $payment
     * @param modUser           $user
     *
     * @return CreateInvoiceResponse
     *
     * @throws Exception
     * @throws RequestException
     * @throws WrongDataException
     */
    public function createInvoice(RBKmoneyRecurrent $payment, modUser $user)
    {
        $date = new DateTime();
        $amount = $payment->get('amount');
        $paymentSystem = $this->modx->getObject(MS_PAYMENT_CLASS, ['class' => 'RBKmoneyPaymentHandler']);

        /**
         * @var $order msOrder
         */
        $order = $this->modx->newObject(MS_ORDER_CLASS, [
            'payment_system_id' => 'RBKmoney',
            'user_id' => $user->get('id'),
            'createdon' => $date->format('Y-m-d H:i:s'),
            'cost' => $amount,
            'cart_cost' => $amount,
            'payment' => $paymentSystem->get('id'),
        ]);
        $order->save();
        $order->set('num', $date->format('ym') . "/{$order->get('id')}");

        /**
         * @var $order msOrderProduct
         */
        $orderItem = $this->modx->newObject(MS_ORDER_PRODUCT_CLASS, [
            'product_id' => $payment->get('order_id'),
            'order_id' => $order->get('id'),
            'name' => $payment->get('name'),
            'count' => 1,
            'price' => $amount,
            'cost' => $amount,
        ]);
        $orderItem->save();
        $order->updateProducts();

        $endDate = new DateTime();
        $shopId = $this->settings['shopId'];
        $product = RBK_MONEY_ORDER_PAYMENT . " №{$order->get('id')} " . $_SERVER['HTTP_HOST'];
        $version = include $this->modx->getOption('core_path') . 'docs/version.inc.php';

        $createInvoice = new CreateInvoiceRequest(
            $shopId,
            $endDate->add(new DateInterval(INVOICE_LIFETIME_DATE_INTERVAL_SETTING)),
            $payment->get('currency'),
            $product,
            new Metadata([
                'orderId' => $order->get('id'),
                'cms' => "MODX {$version['code_name']}",
                'cms_version' => $version['full_version'],
                'module' => MODULE_NAME_SETTING,
                'module_version' => MODULE_VERSION_SETTING,
            ])
        );

        if (RBK_MONEY_PARAMETER_USE === $this->settings['fiscalization']) {
            $cart = new Cart(
                "{$orderItem->get('name')} ({$orderItem->get('count')})",
                $orderItem->get('count'),
                $this->prepareAmount($amount)
            );

            $vat = $payment->get('vat_rate');

            if (!empty($vat) && RBK_MONEY_PARAMETER_NOT_USE !== $vat) {
                $cart->setTaxMode(new TaxMode($vat));
            }

            $createInvoice->addCart($cart);
        } else {
            $createInvoice->setAmount($this->prepareAmount($amount));
        }

        return $this->sender->sendCreateInvoiceRequest($createInvoice);
    }

    /**
     * @param float $price
     *
     * @return string
     */
    private function prepareAmount($price)
    {
        return number_format($price, 2, '', '');
    }

    /**
     * @param CreateInvoiceResponse $invoice
     * @param string                $customerId
     *
     * @throws RequestException
     * @throws WrongDataException
     * @throws WrongRequestException
     */
    public function createPayment(CreateInvoiceResponse $invoice, $customerId)
    {
        $payRequest = new CreatePaymentRequest(
            new PaymentFlowInstantRequest(),
            new CustomerPayerRequest($customerId),
            $invoice->id
        );

        $this->sender->sendCreatePaymentRequest($payRequest);
    }
}


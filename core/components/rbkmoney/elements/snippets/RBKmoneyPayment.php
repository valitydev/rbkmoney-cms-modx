<?php

use Exception;
use src\Api\Exceptions\WrongDataException;
use src\Api\Exceptions\WrongRequestException;
use src\Api\Invoices\CreateInvoice\Cart;
use src\Api\Invoices\CreateInvoice\Request\CreateInvoiceRequest;
use src\Api\Invoices\CreateInvoice\Response\CreateInvoiceResponse;
use src\Api\Invoices\CreateInvoice\TaxMode;
use src\Api\Metadata;
use src\Api\Payments\CreatePayment\HoldType;
use src\Api\Webhooks\CreateWebhook\Request\CreateWebhookRequest;
use src\Api\Webhooks\CustomersTopicScope;
use src\Api\Webhooks\GetWebhooks\Request\GetWebhooksRequest;
use src\Api\Webhooks\InvoicesTopicScope;
use src\Api\Webhooks\WebhookResponse\WebhookResponse;
use src\Client\Client;
use src\Client\Sender;
use src\Exceptions\RequestException;

/**
 * @var $order msOrder
 */
$order = $modx->getObject('msOrder', ['id' => $_GET['orderId']]);
$payment = new RBKmoneyPayment($modx);

return $payment->getPaymentForm($order);

class RBKmoneyPayment
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

    /**
     * @param modX $modx
     */
    function __construct(modX $modx)
    {
        $this->modx = $modx;
        $this->corePath = $this->modx->getOption(
            'rbkmoney_core_path',
            null,
            $this->modx->getOption('core_path') . 'components/rbkmoney/'
        );

        $lang = $this->modx->getOption('manager_language');
        if (!file_exists($this->corePath . "lang/settings.$lang.php")) {
            $lang = 'en';
        }

        require_once $this->corePath . "lang/settings.$lang.php";
        require_once $this->corePath . 'src/settings.php';
        require_once $this->corePath . 'src/autoload.php';

        $this->loadRBKmoneyClasses();

        $settings = $this->modx->getCollection(RBK_MONEY_SETTINGS_CLASS);

        /**
         * @var $setting RBKmoneySettings
         */
        foreach ($settings as $setting) {
            $this->settings[$setting->get('code')] = $setting->get('value');
        }

        $callbackPath = $this->modx->makeUrl($this->settings['callbackPageId']);
        $currentSchema = ((isset($_SERVER['HTTPS']) && preg_match("/^on$/i", $_SERVER['HTTPS'])) ? 'https' : 'http');
        $this->settings['callbackUrl'] = "$currentSchema://{$_SERVER['HTTP_HOST']}/$callbackPath";
        $this->settings['successUrl'] = "$currentSchema://{$_SERVER['HTTP_HOST']}";

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
     * @param msOrder $order
     *
     * @return string
     *
     * @throws RequestException
     * @throws WrongDataException
     * @throws WrongRequestException
     * @throws Exception
     */
    public function getPaymentForm(msOrder $order)
    {
        $shopId = $this->settings['shopId'];
        $orderId = $order->get('id');
        $product = RBK_MONEY_ORDER_PAYMENT . " №$orderId {$_SERVER['HTTP_HOST']}";

        $necessaryWebhooks = $this->getNecessaryWebhooks();

        if (!empty($necessaryWebhooks[InvoicesTopicScope::INVOICES_TOPIC])) {
            $this->createPaymentWebhook(
                $necessaryWebhooks[InvoicesTopicScope::INVOICES_TOPIC]
            );
        }

        $rbkMoneyInvoice = $this->modx->getObject(RBK_MONEY_INVOICE_CLASS, ['order_id' => $order->id]);

        if (!empty($rbkMoneyInvoice)) {
            // Даем пользователю 5 минут на заполнение даных карты
            $diff = new DateInterval(END_INVOICE_INTERVAL_SETTING);
            $endDate = new DateTime($rbkMoneyInvoice->get('end_date'));

            if ($endDate->sub($diff) > new DateTime()) {
                $payload = $rbkMoneyInvoice->get('payload');
                $invoiceId = $rbkMoneyInvoice->get('invoice_id');
            }
        }

        /**
         * @var $user modUser
         */
        $user = $order->getOne('User');

        if (empty($payload)) {
            $invoiceResponse = $this->createInvoice($order, $product);

            if (!empty($necessaryWebhooks[CustomersTopicScope::CUSTOMERS_TOPIC])) {
                $this->createCustomerWebhook(
                    $shopId,
                    $necessaryWebhooks[CustomersTopicScope::CUSTOMERS_TOPIC]
                );
            }
            include $this->corePath . 'src/Customers.php';

            $customers = new Customers($this->sender, $this->modx);
            $customer = $customers->createRecurrent($order, $user->get('id'), $invoiceResponse);

            $payload = $invoiceResponse->payload;
            $invoiceId = $invoiceResponse->id;
        }

        if (empty($customer)) {
            $out = 'data-invoice-id="' . $invoiceId . '"
                data-invoice-access-token="' . $payload . '"';
        } else {
            $out = $customer;
        }

        ob_end_clean();

        $holdExpiration = '';
        if ($holdType = (RBK_MONEY_PAYMENT_TYPE_HOLD === $this->settings['paymentType'])) {
            $holdExpiration = 'data-hold-expiration="' . $this->getHoldType()->getValue() . '"';
        }

        // При echo true заменяется на 1, а checkout воспринимает только true
        $holdType = $holdType ? 'true' : 'false';
        $requireCardHolder = (RBK_MONEY_SHOW_PARAMETER === $this->settings['cardHolder']) ? 'true' : 'false';
        $shadingCvv = (RBK_MONEY_SHOW_PARAMETER === $this->settings['shadingCvv']) ? 'true' : 'false';

        return '
<div align="center" style="margin-top: 20%">
' . RBK_MONEY_REDIRECT_TO_PAYMENT_PAGE . '<br>
' . RBK_MONEY_CLICK_BUTTON_PAY . '
<form action="' . $this->settings['successUrl'] . '" name="pay_form" method="GET">
                <input type="hidden" name="id" value="' . $this->settings['successPageId'] . '">
            <script src="' . RBK_MONEY_CHECKOUT_URL_SETTING . '" class="rbkmoney-checkout"
                    data-payment-flow-hold="' . $holdType . '"
                    data-obscure-card-cvv="' . $shadingCvv . '"
                    data-require-card-holder="' . $requireCardHolder . '"
                    ' . $holdExpiration . '
                    data-name="' . $product . '"
                    data-email="' . $user->Profile->email . '"
                    data-description="' . $product . '"
                    ' . $out . '
                    data-label="' . RBK_MONEY_PAY . '">
            </script>
        </form>
        <script>window.onload = function() {
             document.getElementById("rbkmoney-button").click();
          };
        </script>
</div>';
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
     * @return array
     *
     * @throws RequestException
     * @throws WrongDataException
     * @throws WrongRequestException
     */
    private function getNecessaryWebhooks()
    {
        $webhooks = $this->sender->sendGetWebhooksRequest(new GetWebhooksRequest());

        $statuses = [
            InvoicesTopicScope::INVOICES_TOPIC => [
                InvoicesTopicScope::INVOICE_PAID,
                InvoicesTopicScope::PAYMENT_PROCESSED,
                InvoicesTopicScope::PAYMENT_CAPTURED,
                InvoicesTopicScope::INVOICE_CANCELLED,
                InvoicesTopicScope::PAYMENT_REFUNDED,
                InvoicesTopicScope::PAYMENT_CANCELLED,
                InvoicesTopicScope::PAYMENT_PROCESSED,
            ],
            CustomersTopicScope::CUSTOMERS_TOPIC => [
                CustomersTopicScope::CUSTOMER_READY,
            ],
        ];

        /**
         * @var $webhook WebhookResponse
         */
        foreach ($webhooks->webhooks as $webhook) {
            if (empty($webhook) || $this->settings['callbackUrl'] !== $webhook->url) {
                continue;
            }
            if (InvoicesTopicScope::INVOICES_TOPIC === $webhook->scope->topic) {
                $statuses[InvoicesTopicScope::INVOICES_TOPIC] = array_diff(
                    $statuses[InvoicesTopicScope::INVOICES_TOPIC],
                    $webhook->scope->eventTypes
                );
            } else {
                $statuses[CustomersTopicScope::CUSTOMERS_TOPIC] = array_diff(
                    $statuses[CustomersTopicScope::CUSTOMERS_TOPIC],
                    $webhook->scope->eventTypes
                );
            }
        }

        if (empty($statuses[InvoicesTopicScope::INVOICES_TOPIC]) && empty($statuses[CustomersTopicScope::CUSTOMERS_TOPIC])) {
            $publicKey = $this->modx->getObject(RBK_MONEY_SETTINGS_CLASS, ['code' => 'publicKey']);

            $publicKey->set('value', $webhook->publicKey);
            $publicKey->save();
        }

        return $statuses;
    }

    /**
     * @param array $types
     *
     * @return void
     *
     * @throws RequestException
     * @throws WrongDataException
     * @throws WrongRequestException
     */
    private function createPaymentWebhook(array $types)
    {
        $invoiceScope = new InvoicesTopicScope($this->settings['shopId'], $types);

        $webhook = $this->sender->sendCreateWebhookRequest(
            new CreateWebhookRequest($invoiceScope, $this->settings['callbackUrl'])
        );

        $publicKey = $this->modx->getObject(RBK_MONEY_SETTINGS_CLASS, ['code' => 'publicKey']);

        $publicKey->set('value', $webhook->publicKey);
        $publicKey->save();
    }

    /**
     * @param string $shopId
     * @param array  $types
     *
     * @return void
     *
     * @throws RequestException
     * @throws WrongDataException
     * @throws WrongRequestException
     */
    private function createCustomerWebhook($shopId, array $types)
    {
        $scope = new CustomersTopicScope($shopId, $types);

        $webhook = $this->sender->sendCreateWebhookRequest(
            new CreateWebhookRequest($scope, $this->settings['callbackUrl'])
        );

        $publicKey = $this->modx->getObject(RBK_MONEY_SETTINGS_CLASS, ['code' => 'publicKey']);

        $publicKey->set('value', $webhook->publicKey);
        $publicKey->save();
    }

    /**
     * @param msOrder $order
     * @param string  $product
     *
     * @return CreateInvoiceResponse
     *
     * @throws Exception
     * @throws RequestException
     * @throws WrongDataException
     * @throws WrongRequestException
     */
    private function createInvoice(msOrder $order, $product)
    {
        $fiscalization = (RBK_MONEY_PARAMETER_USE === $this->settings['fiscalization']);
        $carts = [];
        $sum = 0;

        /**
         * @var $item msProduct
         */
        foreach ($order->getMany('Products') as $item) {
            $quantity = $item->get('count');
            $itemName = $item->get('name');
            $price = $item->get('price');

            $sum += $price;

            if ($fiscalization) {
                $cart = new Cart(
                    "$itemName ($quantity)",
                    $quantity,
                    $this->prepareAmount($item->get('price'))
                );

                $vatRate = $this->settings['vatRate'];

                if (RBK_MONEY_PARAMETER_NOT_USE === $vatRate || empty($vatRate)) {
                    $carts[] = $cart;

                    continue;
                }

                $carts[] = $cart->setTaxMode(new TaxMode($vatRate));
            }
        }

        if ($sum === 0) {
            throw new WrongDataException(RBK_MONEY_ERROR_AMOUNT_IS_NOT_VALID, RBK_MONEY_HTTP_CODE_BAD_REQUEST);
        }

        $endDate = new DateTime();

        $version = include $this->modx->getOption('core_path') . 'docs/version.inc.php';

        $createInvoice = new CreateInvoiceRequest(
            $this->settings['shopId'],
            $endDate->add(new DateInterval(INVOICE_LIFETIME_DATE_INTERVAL_SETTING)),
            $this->settings['currency'],
            $product,
            new Metadata([
                'orderId' => $order->get('id'),
                'cms' => "MODX {$version['code_name']}",
                'cms_version' => $version['full_version'],
                'module' => MODULE_NAME_SETTING,
                'module_version' => MODULE_VERSION_SETTING,
            ])
        );

        if (0 != $order->get('delivery_cost')) {
            $deliveryCart = new Cart(
                RBK_MONEY_DELIVERY,
                1,
                $this->prepareAmount($order->get('delivery_cost'))
            );
        }

        if ($fiscalization) {
            $deliveryVatRate = $this->settings['deliveryVatRate'];

            if (RBK_MONEY_PARAMETER_NOT_USE !== $deliveryVatRate && !empty($deliveryVatRate && isset($deliveryCart))) {
                $carts[] = $deliveryCart->setTaxMode(new TaxMode($deliveryVatRate));
            }
            $createInvoice->addCarts($carts);
        } else {
            $createInvoice->setAmount($this->prepareAmount($order->get('cost')));
        }

        $invoice = $this->sender->sendCreateInvoiceRequest($createInvoice);

        $this->saveInvoice($invoice, $order);

        return $invoice;
    }

    /**
     * @param CreateInvoiceResponse $invoice
     * @param msOrder               $order
     *
     * @return void
     */
    private function saveInvoice(CreateInvoiceResponse $invoice, msOrder $order)
    {
        $this->modx->newObject(RBK_MONEY_INVOICE_CLASS, [
            'invoice_id' => $invoice->id,
            'payload' => $invoice->payload,
            'end_date' => $invoice->endDate->format('Y-m-d H:i:s'),
            'order_id' => $order->get('id'),
        ])->save();
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

}
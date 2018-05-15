<?php

use src\Api\ContactInfo;
use src\Api\Customers\CreateCustomer\Request\CreateCustomerRequest;
use src\Api\Customers\CustomerResponse\CustomerResponse;
use src\Api\Exceptions\WrongDataException;
use src\Api\Exceptions\WrongRequestException;
use src\Api\Invoices\CreateInvoice\Response\CreateInvoiceResponse;
use src\Api\Metadata;
use src\Client\Sender;
use src\Exceptions\RequestException;

class Customers
{

    /**
     * @var Sender
     */
    private $sender;

    /**
     * @var array
     */
    private $settings;

    /**
     * @var modX
     */
    private $modx;

    /**
     * @param Sender $sender
     */
    public function __construct(Sender $sender, modX $modx)
    {
        $this->modx = $modx;

        $settings = $modx->getCollection(RBK_MONEY_SETTINGS_CLASS);

        /**
         * @var $setting RBKmoneySettings
         */
        foreach ($settings as $setting) {
            $this->settings[$setting->get('code')] = $setting->get('value');
        }

        $this->sender = $sender;
    }

    /**
     * @return array
     */
    private function getRecurrentItems()
    {
        $recurrent = $this->modx->getCollection(RBK_MONEY_RECURRENT_ITEMS_CLASS);

        if (empty($recurrent)) {
            return [];
        }

        $result = '';

        /**
         * @var $item RBKmoneyRecurrentItems
         */
        foreach ($recurrent as $item) {
            $result .= $item->get('article') . PHP_EOL;
        }

        return explode(PHP_EOL, trim($result));
    }

    /**
     * @param msOrder               $order
     * @param                       $userId
     * @param CreateInvoiceResponse $invoiceResponse
     *
     * @return array
     *
     * @throws RequestException
     * @throws WrongDataException
     * @throws WrongRequestException
     */
    private function createCustomer(
        msOrder $order,
        $userId,
        CreateInvoiceResponse $invoiceResponse
    ) {
        /**
         * @var $user modUser
         */
        $user = $order->getOne('User');

        $contactInfo = new ContactInfo();

        if (!empty($email = $user->Profile->email)) {
            $contactInfo->setEmail($email);
        }
        if (!empty($phone = $user->Profile->phone)) {
            $contactInfo->setPhone($phone);
        }

        $version = include $this->modx->getOption('core_path') . 'docs/version.inc.php';

        $metadata = new Metadata([
            'shop' => $_SERVER['HTTP_HOST'],
            'userId' => $user->get('id'),
            'firstInvoiceId' => $invoiceResponse->id,
            'cms' => "MODX {$version['code_name']}",
            'cms_version' => $version['full_version'],
            'module' => MODULE_NAME_SETTING,
            'module_version' => MODULE_VERSION_SETTING,
        ]);

        $createCustomer = $this->sender->sendCreateCustomerRequest(new CreateCustomerRequest(
            $this->settings['shopId'],
            $contactInfo,
            $metadata
        ));

        $customerParams = [
            'user_id' => $userId,
            'customer_id' => $createCustomer->customer->id,
            'status' => $createCustomer->customer->status->getValue(),
        ];

        $customer = $this->modx->newObject(RBK_MONEY_RECURRENT_CUSTOMERS_CLASS, $customerParams);
        $customer->save();

        $customerParams += [
            'hash' => $createCustomer->payload,
            'id' => $customer->get('id'),
        ];

        return $customerParams;
    }

    /**
     * @param msOrder               $order
     * @param                       $userId
     * @param CreateInvoiceResponse $invoiceResponse
     *
     * @return null | string
     *
     * @throws RequestException
     * @throws WrongDataException
     * @throws WrongRequestException
     */
    public function createRecurrent(
        msOrder $order,
        $userId,
        CreateInvoiceResponse $invoiceResponse
    ) {
        $articles = [];
        $resultCustomer = null;

        /**
         * @var $item msProduct
         */
        foreach ($order->getMany('Products') as $item) {
            $product = $this->modx->getObject('msProductData', ['id' => $item->get('product_id')]);
            $articles[$item->get('item_price')] = $product->get('article');

            $items[$product->get('article')] = [
                'amount' => $item->get('price'),
                'name' => $item->get('name'),
                'currency' => $this->settings['currency'],
                'date' => new DateTime(),
                'status' => RECURRENT_UNREADY_STATUS,
                'order_id' => $order->get('id'),
            ];
        }
        $intersections = array_intersect($articles, $this->getRecurrentItems());

        if (!empty($intersections)) {
            $customer = $this->modx->getObject(RBK_MONEY_RECURRENT_CUSTOMERS_CLASS, ['user_id' => $userId]);

            if (empty($customer)) {
                $customer = $this->createCustomer($order, $userId, $invoiceResponse);
            } else {
                $customer->toArray();
            }

            foreach ($intersections as $article) {
                $this->saveRecurrent($customer['id'], $items[$article]);
            }
        }

        if (!empty($customer['hash'])) {
            $resultCustomer = 'data-customer-id="' . $customer['customer_id'] . '"
            data-customer-access-token="' . $customer['hash'] . '"';
        }

        return $resultCustomer;
    }

    /**
     * @param string $recurrentCustomerId
     * @param array  $item
     *
     * @return void
     */
    private function saveRecurrent($recurrentCustomerId, array $item)
    {
        $this->modx->newObject(RBK_MONEY_RECURRENT_CLASS, [
            'recurrent_customer_id' => $recurrentCustomerId,
            'amount' => $item['amount'],
            'name' => $item['name'],
            'currency' => $item['currency'],
            'vat_rate' => $this->settings['vatRate'],
            'delivery_vat_rate' => $this->settings['deliveryVatRate'],
            'date' => $item['date']->format('Y.m.d H:i:s'),
            'status' => $item['status'],
            'order_id' => $item['order_id'],
        ])->save();
    }

    /**
     * @param msOrder $order
     * @param modX    $modx
     */
    public function setRecurrentReadyStatuses(msOrder $order, modX $modx)
    {
        $articles = [];
        $recurrent = $modx->getObject(RBK_MONEY_RECURRENT_CLASS, ['order_id' => $order->get('id')]);

        if (!empty($recurrent)) {

            /**
             * @var $item msOrderProduct
             */
            foreach ($order->getMany('Products') as $item) {
                $product = $modx->getObject('msProductData', ['id' => $item->get('product_id')]);

                $articles[$item->get('price')] = $product->get('article');
            }

            $intersections = array_intersect(
                $articles,
                $this->getRecurrentItems()
            );

            if (!empty($intersections)) {
                $recurrent->set('status', RECURRENT_READY_STATUS);
                $recurrent->save();
            }
        }
    }

}

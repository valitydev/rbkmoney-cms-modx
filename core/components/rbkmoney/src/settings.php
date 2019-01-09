<?php
const RBK_MONEY_API_URL_SETTING = 'https://api.rbk.money/v2';
const RBK_MONEY_CHECKOUT_URL_SETTING = 'https://checkout.rbk.money/checkout.js';
const INVOICE_LIFETIME_DATE_INTERVAL_SETTING = 'PT2H';
const END_INVOICE_INTERVAL_SETTING = 'PT5M';
const MODULE_NAME_SETTING = 'RBKmoney';
const MODULE_VERSION_SETTING = '1.0';
const FULL_DATE_FORMAT = 'Y-m-d H:i:s';
const PROPERTY_PAYMENT_TOOL_DETAILS = 'paymentToolDetails';
const PROPERTY_CLIENT_INFO = 'clientInfo';
const PROPERTY_PHONE_NUMBER = 'phoneNumber';
const PROPERTY_EMAIL = 'email';
const PROPERTY_IP = 'ip';
const PROPERTY_TAX_MODE = 'taxMode';
const PROPERTY_COST = 'cost';
const PROPERTY_ID = 'id';
const PROPERTY_STATUS = 'status';
const PROPERTY_ACTIVE = 'active';
const PROPERTY_PUBLIC_KEY = 'publicKey';
const PROPERTY_ERROR = 'error';
const PROPERTY_REASON = 'reason';
const PROPERTY_DESCRIPTION = 'description';
const PROPERTY_INVOICE_TEMPLATE_ID = 'invoiceTemplateID';
const PROPERTY_CART = 'cart';
const PROPERTY_PAYMENT_TOOL = 'paymentTool';
const PROPERTY_CONTINUATION_TOKEN = 'continuationToken';
const PROPERTY_RESULT = 'result';
const PROPERTY_SHOP_ID = 'shopID';
const PROPERTY_FEE = 'fee';
const PROPERTY_GEO_LOCATION_INFO = 'geoLocationInfo';
const PROPERTY_METADATA = 'metadata';
const RECURRENT_READY_STATUS = 'ready';
const RECURRENT_UNREADY_STATUS = 'unready';
const TRANSACTION_DATE_FORMAT = 'd.m.Y';
const MINIMAL_PHP_VERSION = 50500;
const RBK_MONEY_HTTP_CODE_OK = 200;
const RBK_MONEY_HTTP_CODE_BAD_REQUEST = 400;
const RBK_MONEY_HTTP_CODE_FORBIDDEN = 403;
const LOG_FILE_COMMENT = 'Отправьте этот файл в support@rbkmoney.ru';
const RBK_MONEY_CURRENCY_VALUES = [
    'RUB',
    'USD',
    'EUR',
];

/**
 * Ключи классов
 */
const RBK_MONEY_SETTINGS_CLASS = 'RBKmoneySettings';
const RBK_MONEY_RECURRENT_CUSTOMERS_CLASS = 'RBKmoneyRecurrentCustomers';
const RBK_MONEY_RECURRENT_ITEMS_CLASS = 'RBKmoneyRecurrentItems';
const RBK_MONEY_RECURRENT_CLASS = 'RBKmoneyRecurrent';
const RBK_MONEY_INVOICE_CLASS = 'RBKmoneyInvoice';
const MS_ORDER_CLASS = 'msOrder';
const MS_ORDER_STATUS_CLASS = 'msOrderStatus';
const MODX_USER_CLASS = 'modUser';
const MS_ORDER_PRODUCT_CLASS = 'msOrderProduct';
const MS_PAYMENT_CLASS = 'msPayment';
const LOG_FILE_NAME = 'logs.txt';
define('LOG_FILE_PATH', "{$_SERVER['DOCUMENT_ROOT']}/core/components/rbkmoney/logs");

if (!defined('PHP_VERSION_ID')) {
    $version = explode('.', PHP_VERSION);

    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}
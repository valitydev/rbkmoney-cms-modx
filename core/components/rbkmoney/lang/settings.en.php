<?php
const RBK_MONEY_TRANSACTIONS = 'Transactions';
const RBK_MONEY_SAVE = 'Save';
const RBK_MONEY_RESET = 'Reset';
const RBK_MONEY_NEXT = 'Next';
const RBK_MONEY_PAY = 'Pay';
const RBK_MONEY_PREVIOUS = 'Previous';
const RBK_MONEY_WRONG_VALUE = 'Wrong value';
const RBK_MONEY_WRONG_SIGNATURE = 'Wrong signature';
const RBK_MONEY_INSTALLATION_SUCCESS = 'Module installation was successful';
const RBK_MONEY_SETTINGS = 'Settings';
const RBK_MONEY_RECURRENT = 'Recurrent payments';
const RBK_MONEY_RECURRENT_ITEMS = 'Items for recurrent payments';
const RBK_MONEY_API_KEY = 'API key';
const RBK_MONEY_ITEM_IDS = 'Articles of products for recurrent payments';
const RBK_MONEY_SHOP_ID = 'Shop ID';
const RBK_MONEY_SUCCESS_PAGE_ID = 'Successful payment page ID';
const RBK_MONEY_PAYMENT_TYPE = 'Payment type';
const RBK_MONEY_SHOW_PARAMETER = 'Yes';
const RBK_MONEY_NOT_SHOW_PARAMETER = 'No';
const RBK_MONEY_CARD_HOLDER = 'Show cardholder on payment form';
const RBK_MONEY_HOLD_EXPIRATION = 'Write-off at the end of the holding period';
const RBK_MONEY_PROCESSED = 'Processed';
const RBK_MONEY_PAID = 'Paid';
const RBK_MONEY = 'RBKmoney';
const RBK_MONEY_ERROR_SHOP_ID_IS_NOT_VALID = 'Invalid value of the `shopID` parameter';
const RBK_MONEY_ERROR_API_KEY_IS_NOT_VALID = 'Invalid value of the `apiKey` parameter';
const RBK_MONEY_ERROR_SUCCESS_URL_IS_NOT_VALID = 'Invalid value of the `successUrl` parameter';
const RBK_MONEY_ERROR_PAYMENT_TYPE_IS_NOT_VALID = 'Invalid value of the `paymentType` parameter';
const RBK_MONEY_ERROR_HOLD_STATUS_IS_NOT_VALID = 'Invalid value of the `holdStatus` parameter';
const RBK_MONEY_ERROR_AMOUNT_IS_NOT_VALID = 'Invalid value of the `amount` parameter';
const RBK_MONEY_ERROR_HOLD_EXPIRATION_IS_NOT_VALID = 'Invalid value of the `holdExpiration` parameter';
const RBK_MONEY_ERROR_TAX_RATE_IS_NOT_VALID = 'Invalid value `VAT rate` for product: ';
const RBK_MONEY_PAYMENT_TYPE_HOLD = 'Hold';
const RBK_MONEY_PAYMENT_TYPE_INSTANTLY = 'Write-off instantly';
const RBK_MONEY_EXPIRATION_PAYER = 'Payer';
const RBK_MONEY_EXPIRATION_SHOP = 'Shop';
const RBK_MONEY_RECURRENT_DELETED = 'Recurrent payment deleted';
const RBK_MONEY_DATE_FILTER_FROM = 'Date from';
const RBK_MONEY_DATE_FILTER_TO = 'Date to';
const RBK_MONEY_FILTER_SUBMIT = 'Search';
const RBK_MONEY_TRANSACTION_ID = 'ID';
const RBK_MONEY_TRANSACTION_PRODUCT = 'Product';
const RBK_MONEY_TRANSACTION_STATUS = 'Status';
const RBK_MONEY_TRANSACTION_AMOUNT = 'Amount';
const RBK_MONEY_TRANSACTION_CREATED_AT = 'Created at';
const RBK_MONEY_PAYMENT_CONFIRMED = 'Payment confirmed';
const RBK_MONEY_PAYMENT_CANCELLED = 'Payment cancelled';
const RBK_MONEY_REFUND_CREATED = 'Payment refund created';
const RBK_MONEY_PAYMENT_CAPTURE_ERROR = 'Payment confirmation error';
const RBK_MONEY_PAYMENT_CANCELLED_ERROR = 'Payment cancelled error';
const RBK_MONEY_REFUND_CREATE_ERROR = 'Refund create error';
const RBK_MONEY_USER_FIELD = 'User';
const RBK_MONEY_AMOUNT_FIELD = 'Amount';
const RBK_MONEY_PRODUCT_FIELD = 'Product';
const RBK_MONEY_RECURRENT_CREATE_DATE = 'Created at';
const RBK_MONEY_FORM_BUTTON_DELETE = 'Delete';
const RBK_MONEY_FISCALIZATION = 'Fiscalization (54 Federal Law)';
const RBK_MONEY_PARAMETER_USE = 'Use';
const RBK_MONEY_PARAMETER_NOT_USE = "Don't use";
const RBK_MONEY_ORDER_PAYMENT = 'Order payment';
const RBK_MONEY_ORDER_PENDING = 'Your order is pending payment. Go to the';
const RBK_MONEY_WEBSITE = 'website';
const RBK_MONEY_STATUS_STARTED = 'started';
const RBK_MONEY_STATUS_PROCESSED = 'processed';
const RBK_MONEY_STATUS_CAPTURED = 'captured';
const RBK_MONEY_STATUS_CANCELLED = 'cancelled';
const RBK_MONEY_STATUS_CHARGED_BACK = 'charged back';
const RBK_MONEY_STATUS_REFUNDED = 'refunded';
const RBK_MONEY_STATUS_FAILED = 'failed';
const RBK_MONEY_REFUNDED_BY_ADMIN = 'Refunded by admin';
const RBK_MONEY_CANCELLED_BY_ADMIN = 'Cancelled by admin';
const RBK_MONEY_CAPTURED_BY_ADMIN = 'Captured by admin';
const RBK_MONEY_SETTINGS_SAVED = 'Settings saved';
const RBK_MONEY_SAVED = 'Saved';
const RBK_MONEY_SETTINGS_SAVE_ERROR = 'Not all fields have been saved';
const RBK_MONEY_ERROR = 'Error';
const RBK_MONEY_CONFIRM_PAYMENT = 'Confirm payment';
const RBK_MONEY_CANCEL_PAYMENT = 'Cancel payment';
const RBK_MONEY_CREATE_PAYMENT_REFUND = 'Create payment refund';
const RBK_MONEY_CUSTOMER_READY = 'Ready';
const RBK_MONEY_CUSTOMER_UNREADY = 'Unready';
const RBK_MONEY_RECURRENT_SUCCESS = 'Recurrent payment success: ';
const RBK_MONEY_SHADING_CVV = 'Shading card cvv code';
const RBK_MONEY_RECURRENT_PAYMENT = 'Recurrent payment';
const RBK_MONEY_RESPONSE_NOT_RECEIVED = 'Response from RBKmoney is not received';
const RBK_MONEY_REDIRECT_TO_PAYMENT_PAGE = 'Now you will be redirected to the payment page.';
const RBK_MONEY_CLICK_BUTTON_PAY = 'If this does not happen - click on the button "Pay"';
const RBK_MONEY_ERROR_MESSAGE_PHP_VERSION = 'The module requires PHP 5.5 or higher';
const RBK_MONEY_ERROR_MESSAGE_CURL = 'The module requires curl';
const RBK_MONEY_SUCCESS_ORDER_STATUS = 'Order status after payment';
const RBK_MONEY_HOLD_ORDER_STATUS = 'Order status with hold';
const RBK_MONEY_CANCEL_ORDER_STATUS = 'Order status after cancel hold';
const RBK_MONEY_REFUND_ORDER_STATUS = 'Order status after create refund';
const RBK_MONEY_VAT_RATE = 'Vat rate';
const RBK_MONEY_DELIVERY_VAT_RATE = 'Delivery vat rate';
const RBK_MONEY_DELIVERY = 'Delivery';
const RBK_MONEY_CURRENCY = 'Currency';
const RBK_MONEY_CALLBACK_PAGE_ID = 'Callback page id';
const RBK_MONEY_PAYMENT_PAGE_ID = 'Payment page id';
const RBK_MONEY_LOGS = 'Requests log RBKmoney';
const RBK_MONEY_DELETE_LOGS = 'Delete logs';
const RBK_MONEY_DOWNLOAD_LOGS = 'Download logs';
const RBK_MONEY_LOGS_DELETED = 'Logs deleted';
const RBK_MONEY_SAVE_LOGS = 'Write logs RBKmoney';
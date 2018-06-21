<?php

if ($object->xpdo) {

    /**
     * @var modX $modx
     */
    $modx = &$object->xpdo;

    $objects = [
        'RBKmoneyRecurrentItems',
        'RBKmoneyRecurrent',
        'RBKmoneyRecurrentCustomers',
        'RBKmoneyInvoice',
        'RBKmoneySettings',
    ];

    $tables = [
        'RBKmoney_Recurrent_Items',
        'RBKmoney_Recurrent',
        'RBKmoney_Recurrent_Customers',
        'RBKmoney_Invoice',
        'RBKmoney_Settings',
    ];

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
            $modelPath = $modx->getOption('rbkmoney_core_path', null, $modx->getOption('core_path') . 'components/rbkmoney/') . 'model/';
            $modx->addPackage('rbkmoney', $modelPath);

            $manager = $modx->getManager();

            foreach ($objects as $object) {
                $manager->createObjectContainer($object);
            }

            $settings = [
                [
                    'code' => 'apiKey',
                    'name' => 'RBK_MONEY_API_KEY',
                    'value' => ''
                ],
                [
                    'code' => 'shopId',
                    'name' => 'RBK_MONEY_SHOP_ID',
                    'value' => ''
                ],
                [
                    'code' => 'successPageId',
                    'name' => 'RBK_MONEY_SUCCESS_PAGE_ID',
                    'value' => ''
                ],
                [
                    'code' => 'paymentType',
                    'name' => 'RBK_MONEY_PAYMENT_TYPE',
                    'value' => ''
                ],
                [
                    'code' => 'holdExpiration',
                    'name' => 'RBK_MONEY_HOLD_EXPIRATION',
                    'value' => ''
                ],
                [
                    'code' => 'cardHolder',
                    'name' => 'RBK_MONEY_CARD_HOLDER',
                    'value' => ''
                ],
                [
                    'code' => 'shadingCvv',
                    'name' => 'RBK_MONEY_SHADING_CVV',
                    'value' => ''
                ],
                [
                    'code' => 'fiscalization',
                    'name' => 'RBK_MONEY_FISCALIZATION',
                    'value' => ''
                ],
                [
                    'code' => 'successStatus',
                    'name' => 'RBK_MONEY_SUCCESS_ORDER_STATUS',
                    'value' => ''
                ],
                [
                    'code' => 'holdStatus',
                    'name' => 'RBK_MONEY_HOLD_ORDER_STATUS',
                    'value' => ''
                ],
                [
                    'code' => 'cancelStatus',
                    'name' => 'RBK_MONEY_CANCEL_ORDER_STATUS',
                    'value' => ''
                ],
                [
                    'code' => 'refundStatus',
                    'name' => 'RBK_MONEY_REFUND_ORDER_STATUS',
                    'value' => ''
                ],
                [
                    'code' => 'vatRate',
                    'name' => 'RBK_MONEY_VAT_RATE',
                    'value' => ''
                ],
                [
                    'code' => 'deliveryVatRate',
                    'name' => 'RBK_MONEY_DELIVERY_VAT_RATE',
                    'value' => ''
                ],
                [
                    'code' => 'currency',
                    'name' => 'RBK_MONEY_CURRENCY',
                    'value' => ''
                ],
                [
                    'code' => 'callbackPageId',
                    'name' => 'RBK_MONEY_CALLBACK_PAGE_ID',
                    'value' => ''
                ],
                [
                    'code' => 'paymentPageId',
                    'name' => 'RBK_MONEY_PAYMENT_PAGE_ID',
                    'value' => ''
                ],
                [
                    'code' => 'publicKey',
                    'name' => 'publicKey',
                    'value' => ''
                ],
                [
                    'code' => 'saveLogs',
                    'name' => 'RBK_MONEY_SAVE_LOGS',
                    'value' => ''
                ],
            ];
            $sql = "INSERT INTO {$modx->getTableName('RBKmoneySettings')}(`name`, `code`, `value`) VALUES";

            foreach ($settings as $setting) {
                $sql .= " ('{$setting['name']}', '{$setting['code']}', '{$setting['value']}'), ";
            }

            $c = $modx->prepare(substr($sql, 0, -2));
            $c->execute();
            break;
        case xPDOTransport::ACTION_UPGRADE:
            break;
        case xPDOTransport::ACTION_UNINSTALL:
            $prefix = $modx->config['table_prefix'];
            foreach ($tables as $table) {
                $tableName = $prefix . $table;
                $c = $modx->prepare("DROP TABLE IF EXISTS `$tableName`");
                $c->execute();
            }
            break;
    }
}

return true;

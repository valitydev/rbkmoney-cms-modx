<?php

use src\Api\Invoices\CreateInvoice\TaxMode;

class RBKmoney
{

    /**
     * @var modX
     */
    public $modx;

    /**
     * @var array
     */
    public $config;

    /**
     * @param modX  $modx
     * @param array $config
     */
    function __construct(modX &$modx, array $config = [])
    {
        $this->modx =& $modx;

        $corePath = $this->modx->getOption('rbkmoney_core_path', $config, $this->modx->getOption('core_path') . 'components/rbkmoney/');
        $assetsUrl = $this->modx->getOption('rbkmoney_assets_url', $config, $this->modx->getOption('assets_url') . 'components/rbkmoney/');

        $connectorUrl = $assetsUrl . 'connector.php';

        $lang = $this->modx->getOption('manager_language');
        if (!file_exists($corePath . "lang/settings.$lang.php")) {
            $lang = 'en';
        }

        require $corePath . "lang/settings.$lang.php";
        require $corePath . 'src/autoload.php';
        require $corePath . 'src/settings.php';

        $modelPath = $this->modx->getOption('core_path') . 'components/minishop2/model/';
        $this->modx->addPackage('minishop2', $modelPath);

        $dbClassPath = $corePath . 'model/rbkmoney/';
        $modx->loadClass('RBKmoneyRecurrentItems', $dbClassPath);

        $statuses = [];
        foreach ($this->modx->getCollection('msOrderStatus') as $orderStatus) {
            $statuses[] = [
                'id' => $orderStatus->get('id'),
                'name' => $orderStatus->get('name'),
            ];
        }

        $langConst = [];
        foreach (get_defined_constants() as $key => $value) {
            if (strpos($key, 'RBK_MONEY') === 0) {
                $langConst[$key] = $value;
            }
        }

        $vatRates = TaxMode::$validValues;

        $vatRates[] = RBK_MONEY_PARAMETER_NOT_USE;

        $this->config = array_merge([
            'assetsUrl' => $assetsUrl,
            'cssUrl' => $assetsUrl . 'css/',
            'jsUrl' => $assetsUrl . 'js/',
            'connectorUrl' => $connectorUrl,
            'transactionsUrl' => $assetsUrl . 'transactions.php',
            'downloadLogsUrl' => $assetsUrl . 'downloadLogs.php',
            'dateFrom' => (new DateTime('today'))->format('Y.m.d'),
            'dateTo' => (new DateTime())->setTime(23, 59, 59)->format('Y.m.d'),

            'corePath' => $corePath,
            'orderStatuses' => $statuses,
            'modelPath' => $corePath . 'model/',
            'templatesPath' => $corePath . 'elements/templates/',
            'snippetsPath' => $corePath . 'elements/snippets/',
            'boolValues' => [
                RBK_MONEY_SHOW_PARAMETER,
                RBK_MONEY_NOT_SHOW_PARAMETER
            ],
            'paymentTypeValues' => [
                RBK_MONEY_PAYMENT_TYPE_HOLD,
                RBK_MONEY_PAYMENT_TYPE_INSTANTLY
            ],
            'holdExpirationValues' => [
                RBK_MONEY_EXPIRATION_PAYER,
                RBK_MONEY_EXPIRATION_SHOP
            ],
            'fiscalizationValues' => [
                RBK_MONEY_PARAMETER_USE,
                RBK_MONEY_PARAMETER_NOT_USE
            ],
            'currency' => RBK_MONEY_CURRENCY_VALUES,
            'vatRate' => $vatRates,
            'langConst' => $langConst,
        ], $config);

        $this->modx->addPackage('rbkmoney', $this->config['modelPath']);
    }

}

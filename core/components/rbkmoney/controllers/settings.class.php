<?php

class RBKmoneySettingsManagerController extends RBKmoneyMainController
{

    /**
     * @var RBKmoney
     */
    public $rbkmoney;

    /**
     * @param array $scriptProperties
     */
    public function process(array $scriptProperties = [])
    {
    }

    /**
     * @return null | string
     */
    public function getPageTitle()
    {
        return $this->modx->lexicon('rbkmoney');
    }

    /**
     * @return void
     */
    public function loadCustomCssJs()
    {
        $this->modx->regClientStartupScript($this->rbkmoney->config['jsUrl'] . 'mgr/settings/settings.js');
        $this->modx->regClientStartupScript($this->rbkmoney->config['jsUrl'] . 'mgr/settings/panel.js');
        $this->modx->regClientStartupScript($this->rbkmoney->config['jsUrl'] . 'mgr/settings/options/options.combo.js');
        $this->modx->regClientStartupScript($this->rbkmoney->config['jsUrl'] . 'mgr/settings/options/options.panel.js');
        $this->modx->regClientStartupScript($this->rbkmoney->config['jsUrl'] . 'mgr/settings/recurrent/recurrent.grid.js');
        $this->modx->regClientStartupScript($this->rbkmoney->config['jsUrl'] . 'mgr/settings/recurrentItems/recurrent.items.panel.js');
        $this->modx->regClientStartupScript($this->rbkmoney->config['jsUrl'] . 'mgr/settings/transactions/transactions.grid.js');
        $this->modx->regClientStartupScript($this->rbkmoney->config['jsUrl'] . 'mgr/settings/transactions/transactions.panel.js');
        $this->modx->regClientStartupScript($this->rbkmoney->config['jsUrl'] . 'mgr/settings/logs/logs.panel.js');
    }

    /**
     * @return string
     */
    public function getTemplateFile()
    {
        return $this->rbkmoney->config['templatesPath'] . 'settings.tpl';
    }

}

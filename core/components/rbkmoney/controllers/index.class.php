<?php

abstract class RBKmoneyMainController extends modExtraManagerController
{

    /**
     * @var RBKmoney
     */
    public $rbkmoney;

    /**
     * @return void
     */
    public function initialize()
    {
        $corePath = $this->modx->getOption('rbkmoney_core_path', null, $this->modx->getOption('core_path') . 'components/rbkmoney/');
        require_once $corePath . 'model/rbkmoney.class.php';

        $this->rbkmoney = new RBKmoney($this->modx);

        $this->addCss($this->rbkmoney->config['cssUrl'] . 'mgr/rbkmoney.css');
        $this->addJavascript($this->rbkmoney->config['jsUrl'] . 'mgr/rbkmoney.js');

        $this->addHtml('
		<script type="text/javascript">
			RBKmoney.config = ' . $this->modx->toJSON($this->rbkmoney->config) . ';
			RBKmoney.config.connector_url = "' . $this->rbkmoney->config['connectorUrl'] . '";
		</script>
		
		');

        parent::initialize();
    }

    /**
     * @return array
     */
    public function getLanguageTopics()
    {
        return ['rbkmoney:default'];
    }

    /**
     * @return bool
     */
    public function checkPermissions()
    {
        return true;
    }

}

class IndexManagerController extends RBKmoneyMainController
{

    /**
     * @return string
     */
    public static function getDefaultController()
    {
        return 'settings';
    }

}

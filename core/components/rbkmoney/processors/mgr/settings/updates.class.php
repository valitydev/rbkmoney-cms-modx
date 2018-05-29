<?php

class RBKmoneySettingUpdatesProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'RBKmoneySettings';
    public $classKey = 'RBKmoneySettings';
    public $languageTopics = ['rbkmoney'];
    public $permission = 'update_document';

    public function initialize()
    {
        return true;
    }

    /**
     * @return string
     */
    public function process()
    {
        $corePath = $this->modx->getOption('rbkmoney_core_path', [], $this->modx->getOption('core_path') . 'components/rbkmoney/');

        $lang = $this->modx->getOption('manager_language');
        if (!file_exists($corePath . "lang/settings.$lang.php")) {
            $lang = 'en';
        }

        require $corePath . "lang/settings.$lang.php";

        $properties = $this->getProperties();
        $items = $this->modx->getIterator($this->classKey, $this->modx->newQuery($this->classKey));
        $success = true;

        foreach ($items as $item) {
            $mas = $item->toArray();

            /**
             * @var $object RBKmoneySettings
             */
            $object = $this->modx->getObject($this->classKey, $mas['id']);
            $object->set('value', trim($properties[$mas['code']]));

            if (!$object->save()) {
                $success = false;
            }
        }

        return json_encode([
            'success' => $success,
            'data' => [
                'returnCode' => 0,
            ]
        ]);
    }

}

return 'RBKmoneySettingUpdatesProcessor';
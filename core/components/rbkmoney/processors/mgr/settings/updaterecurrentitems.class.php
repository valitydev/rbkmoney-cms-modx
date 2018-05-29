<?php

class RBKmoneyUpdateRecurrentItemsProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'RBKmoneyRecurrentItems';
    public $classKey = 'RBKmoneyRecurrentItems';
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

        $success = true;

        $properties = $this->getProperties();

        $articles = array_map(function($value) {
            return trim($value);
        }, explode("\n", $properties['recurrentItems']));

        // Удаляем из массива всё, кроме цифр
        $articles = array_filter($articles, function($value) {
            if (preg_match('/^\d+$/', $value)) {
                return true;
            }

            return false;
        });

        /**
         * @var $object RBKmoneyRecurrentItems
         */
        foreach ($this->modx->getCollection($this->classKey) as $object) {
            $object->remove();
        }

        foreach (array_unique($articles) as $article) {
            $object = $this->modx->newObject($this->classKey);
            if (!$object->set('article', $article)) {
                $success = false;
            }

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

return 'RBKmoneyUpdateRecurrentItemsProcessor';
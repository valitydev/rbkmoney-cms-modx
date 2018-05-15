<?php

class RBKmoneySettingGetRecurrentItemsProcessor extends modObjectGetListProcessor
{

    public $objectType = 'RBKmoneyRecurrentItems';
    public $classKey = 'RBKmoneyRecurrentItems';

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'results' => $this->modx->getCollection($this->classKey),
        ];
    }

}

return 'RBKmoneySettingGetRecurrentItemsProcessor';

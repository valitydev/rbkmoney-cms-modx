<?php

class RBKmoneySettingGetListProcessor extends modObjectGetListProcessor
{

    public $objectType = 'RBKmoneySettings';
    public $classKey = 'RBKmoneySettings';

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

return 'RBKmoneySettingGetListProcessor';

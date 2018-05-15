<?php

class RBKmoneyGetRecurrentProcessor extends modObjectGetListProcessor
{

    public $objectType = 'RBKmoneyRecurrent';
    public $classKey = 'RBKmoneyRecurrent';

    /**
     * @return array
     */
    public function getData()
    {
        $limit = intval($this->getProperty('limit'));
        $start = intval($this->getProperty('start'));

        $query = $this->prepareQueryBeforeCount($this->modx->newQuery($this->classKey));
        $data['total'] = $this->modx->getCount($this->classKey, $query);
        $queryAfterCount = $this->prepareQueryAfterCount($query);

        $sortClassKey = $this->getSortClassKey();
        $sortKey = $this->modx->getSelectColumns(
            $sortClassKey,
            $this->getProperty('sortAlias', $sortClassKey),
            '',
            [$this->getProperty('sort')]
        );

        if (empty($sortKey)) {
            $sortKey = $this->getProperty('sort');
        }

        $queryAfterCount->sortby($sortKey, $this->getProperty('dir'));

        if ($limit > 0) {
            $queryAfterCount->limit($limit, $start);
        }

        $data['results'] = $this->modx->getCollection($this->classKey, $queryAfterCount);

        /**
         * @var $result RBKmoneyRecurrent
         */
        foreach ($data['results'] as &$result) {
            $customer = $result->getOne('Customer');
            /**
             * @var $user modUser
             */
            $user = $this->modx->getObject('modUser', $customer->get('user_id'));
            $userId = $user->get('id');
            $userName = $user->Profile->get('fullname');

            $result->set('userName', "<a href='?a=security/user/update&id=$userId'>$userName</a>");
        }

        return $data;
    }

}

return 'RBKmoneyGetRecurrentProcessor';

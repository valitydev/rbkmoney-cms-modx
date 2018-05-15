<?php

if (!class_exists('msPaymentInterface')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/core/components/minishop2/model/minishop2/mspaymenthandler.class.php';
}

class RBKmoneyPaymentHandler extends msPaymentHandler implements msPaymentInterface
{

    public function send(msOrder $order)
    {
        $params = [
            'orderId' => $order->get('id')
        ];

        $corePath = $this->modx->getOption('rbkmoney_core_path', [], $this->modx->getOption('core_path') . 'components/rbkmoney');

        $this->modx->loadClass('RBKmoneySettings', "$corePath/model/rbkmoney/");
        $page = $this->modx->getObject('RBKmoneySettings', ['code' => 'paymentPageId']);

        return $this->success('', ['redirect' => $this->modx->makeUrl($page->get('value'), '', $params)]);
    }

}

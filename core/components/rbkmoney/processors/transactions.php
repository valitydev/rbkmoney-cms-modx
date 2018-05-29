<?php

use src\Api\Exceptions\WrongDataException;
use src\Api\Exceptions\WrongRequestException;
use src\Api\Invoices\GetInvoiceById\Request\GetInvoiceByIdRequest;
use src\Api\Payments\PaymentResponse\Flow;
use src\Api\Search\SearchPayments\Request\SearchPaymentsRequest;
use src\Api\Search\SearchPayments\Response\Payment;
use src\Api\Status;
use src\Client\Client;
use src\Client\Sender;
use src\Exceptions\RequestException;

require_once $_SERVER['DOCUMENT_ROOT'] . '/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

$offset = intval($_POST['start']);
$limit = intval($_POST['limit']);
$dateStart = $_POST['date_start'];
$dateEnd = $_POST['date_end'];

if (empty($dateStart)) {
    $dateFrom = new DateTime('today');
} else {
    $dateFrom = new DateTime($dateStart);
}

if (empty($dateEnd)) {
    $dateTo = new DateTime();
    $dateTo->setTime(23, 59, 59);
} else {
    $dateTo = new DateTime($dateEnd);
}

$transactions = new Transaction($modx);

$result = $transactions->getTransactions($offset, $dateFrom, $dateTo, $limit);

echo json_encode([
    'results' => $result,
]);

class Transaction
{

    /**
     * @var array
     */
    private $settings = [];

    /**
     * @var modX
     */
    private $modx;

    /**
     * @param modX $modx
     */
    public function __construct(modX $modx)
    {
        $this->modx = $modx;
        $corePath = $this->modx->getOption('rbkmoney_core_path', null, $modx->getOption('core_path') . 'components/rbkmoney/');

        /**
         * @var $setting RBKmoneySettings
         */
        foreach ($this->modx->getCollection('RBKmoneySettings') as $setting) {
            $this->settings[$setting->get('code')] = $setting->get('value');
        }

        require_once $corePath . 'src/settings.php';
        require_once $corePath . 'src/autoload.php';
    }

    /**
     * @param int      $offset
     * @param DateTime $fromTime
     * @param DateTime $toTime
     * @param int      $limit
     *
     * @return array
     *
     * @throws RequestException
     * @throws WrongDataException
     * @throws WrongRequestException
     */
    public function getTransactions($offset, DateTime $fromTime, DateTime $toTime, $limit = 10)
    {
        try {
            if (empty($this->settings['apiKey'])) {
                throw new WrongDataException(RBK_MONEY_ERROR_API_KEY_IS_NOT_VALID, RBK_MONEY_HTTP_CODE_BAD_REQUEST);
            }
            if (empty($this->settings['shopId'])) {
                throw new WrongDataException(RBK_MONEY_ERROR_SHOP_ID_IS_NOT_VALID, RBK_MONEY_HTTP_CODE_BAD_REQUEST);
            }
        } catch (WrongDataException $exception) {
            echo $exception->getMessage();
            die;
        }

        if ($fromTime->getTimestamp() > $toTime->getTimestamp()) {
            $fromTime = new DateTime('today');
        }
        if ($fromTime->getTimestamp() >= $toTime->getTimestamp()) {
            $toTime = new DateTime();
            $toTime = $toTime->setTime(23, 59, 59);
        }

        $shopId = $this->settings['shopId'];

        $sender = new Sender(new Client($this->modx, $this->settings['apiKey'], $shopId, RBK_MONEY_API_URL_SETTING));

        $paymentRequest = new SearchPaymentsRequest($shopId, $fromTime, $toTime, $limit);
        $paymentRequest->setOffset($offset);

        $payments = $sender->sendSearchPaymentsRequest($paymentRequest);

        $statuses = [
            'started' => RBK_MONEY_STATUS_STARTED,
            'processed' => RBK_MONEY_STATUS_PROCESSED,
            'captured' => RBK_MONEY_STATUS_CAPTURED,
            'cancelled' => RBK_MONEY_STATUS_CANCELLED,
            'charged back' => RBK_MONEY_STATUS_CHARGED_BACK,
            'refunded' => RBK_MONEY_STATUS_REFUNDED,
            'failed' => RBK_MONEY_STATUS_FAILED,
        ];

        $transactions = [];
        $statusHold = Flow::HOLD;
        $statusCaptured = Status::CAPTURED;
        $statusProcessed = Status::PROCESSED;

        /**
         * @var $payment Payment
         */
        foreach ($payments->result as $payment) {
            $paymentStatus = $payment->status->getValue();
            $flowStatus = $payment->flow->type;
            $invoiceRequest = new GetInvoiceByIdRequest($payment->invoiceId);
            $invoice = $sender->sendGetInvoiceByIdRequest($invoiceRequest);
            $metadata = $invoice->metadata->metadata;
            $firstAction = [];
            $secondAction = [];

            if ($statusProcessed === $paymentStatus && $statusHold === $flowStatus) {
                $firstAction = [
                    'action' => 'confirmPayment',
                    'invoiceId' => $invoice->id,
                    'paymentId' => $payment->id,
                    'name' => RBK_MONEY_CONFIRM_PAYMENT,
                ];
                $secondAction = [
                    'action' => 'cancelPayment',
                    'invoiceId' => $invoice->id,
                    'paymentId' => $payment->id,
                    'name' => RBK_MONEY_CANCEL_PAYMENT,
                ];
            } elseif ($statusCaptured === $paymentStatus) {
                $firstAction = [
                    'action' => 'createRefund',
                    'invoiceId' => $invoice->id,
                    'paymentId' => $payment->id,
                    'name' => RBK_MONEY_CREATE_PAYMENT_REFUND,
                ];
            }

            $transactions[] = [
                'id' => $metadata['orderId'],
                'product' => $invoice->product,
                'status' => $statuses[$payment->status->getValue()],
                'amount' => number_format($payment->amount / 100, 2, '.', ''),
                'createdAt' => $payment->createdAt->format(FULL_DATE_FORMAT),
                'firstAction' => $firstAction,
                'secondAction' => $secondAction,
            ];
        }

        return $transactions;
    }

}

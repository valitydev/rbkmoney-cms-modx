<?php

namespace src\Client;

use modX;
use src\Api\Exceptions\WrongRequestException;
use src\Api\Interfaces\GetRequestInterface;
use src\Api\Interfaces\RequestInterface;
use src\Exceptions\RequestException;
use src\Helpers\Log;
use src\Helpers\Logger;
use src\Interfaces\ClientInterface;
use src\Api\Interfaces\PostRequestInterface;

class Client implements ClientInterface
{

    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_ACCEPTED = 202;
    const HTTP_NO_CONTENT = 204;
    const HTTP_SEE_OTHER = 303;

    /**
     * Успешные коды ответов
     */
    private $successCodes = [
        self::HTTP_OK,
        self::HTTP_CREATED,
        self::HTTP_ACCEPTED,
        self::HTTP_NO_CONTENT,
    ];

    const CONTENT_TYPE = 'Content-Type: application/json; charset=utf-8';
    const AUTHORIZATION = 'Authorization: Bearer ';
    const REQUEST_ID = 'X-Request-ID: ';

    private $headers = [];

    /**
     * Приватный ключ для доступа к API
     *
     * @var string
     */
    private $apiKey;

    /**
     * Id магазина
     *
     * @var string
     */
    private $shopId;

    /**
     * @var string
     */
    private $url;

    /**
     * @var modX
     */
    private $modx;

    /**
     * @param modX $modX
     * @param      $apiKey
     * @param      $shopId
     * @param      $url
     */
    public function __construct(modX $modX, $apiKey, $shopId, $url)
    {
        $this->modx = $modX;
        $this->apiKey = $apiKey;
        $this->shopId = $shopId;
        $this->url = $url;

        $this->setHeaders();
    }

    /**
     * Устанавливает хедеры
     *
     * @return void
     */
    private function setHeaders()
    {
        $this->headers = [
            self::CONTENT_TYPE,
            self::AUTHORIZATION . $this->apiKey,
            self::REQUEST_ID . $this->shopId,
        ];
    }

    /**
     * @param RequestInterface $request
     * @param string           $method
     *
     * @return string
     * @throws RequestException
     * @throws WrongRequestException
     */
    public function sendRequest(RequestInterface $request, $method)
    {
        $params = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $this->headers,
        ];

        if (ClientInterface::HTTP_METHOD_GET === $method) {
            if (!($request instanceof GetRequestInterface)) {
                throw new WrongRequestException(RBK_MONEY_WRONG_VALUE . ' Request');
            }
        } elseif (ClientInterface::HTTP_METHOD_POST === $method) {
            if (!($request instanceof PostRequestInterface)) {
                throw new WrongRequestException(RBK_MONEY_WRONG_VALUE . ' Request');
            }

            $params[CURLOPT_POSTFIELDS] = json_encode($request->toArray());
        }

        return $this->sendCurl($this->url . $request->getPath(), $params);
    }

    /**
     * @param string $url
     * @param array  $options
     *
     * @return string
     *
     * @throws RequestException
     */
    private function sendCurl($url, array $options)
    {
        $ch = curl_init($url);

        $headers = '';

        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($ch, $header_line) use (&$headers) {
            $headers .= trim($header_line);

            return strlen($header_line);
        });

        curl_setopt_array($ch, $options);

        $result = curl_exec($ch);

        $responseInfo = curl_getinfo($ch);

        $corePath = $this->modx->getOption(
            'rbkmoney_core_path',
            null,
            $this->modx->getOption('core_path') . 'components/rbkmoney/'
        );
        $this->modx->loadClass(RBK_MONEY_SETTINGS_CLASS, $corePath . 'model/rbkmoney/');

        $saveLogs = $this->modx->getObject(RBK_MONEY_SETTINGS_CLASS, ['code' => 'saveLogs']);

        curl_close($ch);
        if (RBK_MONEY_SHOW_PARAMETER === $saveLogs->get('value')) {
            $log = new Log(
                $url,
                $options[CURLOPT_CUSTOMREQUEST],
                json_encode($options[CURLOPT_HTTPHEADER], 256),
                $result,
                $headers
            );
            $logger = new Logger();
            $logger->saveLog($log->setRequestBody($options[CURLOPT_POSTFIELDS]));
        }

        if (self::HTTP_SEE_OTHER === $responseInfo['http_code']) {
            return $responseInfo['location'];
        }

        if (false === $result) {
            throw new RequestException(RBK_MONEY_RESPONSE_NOT_RECEIVED);
        } elseif (!in_array($responseInfo['http_code'], $this->successCodes)) {
            throw new RequestException($result, $responseInfo['http_code']);
        }

        return $result;
    }

}
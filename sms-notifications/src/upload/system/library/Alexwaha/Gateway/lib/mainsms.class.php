<?php

/**
 * Класс для работы с сервисом MainSms.ru
 */
class MainSMSLib
{
    public const REQUEST_SUCCESS = 'success';

    public const REQUEST_ERROR = 'error';

    protected $project = null;

    protected $key = null;

    protected $testMode = false;

    protected $url = 'mainsms.ru/api/mainsms';

    protected $useSSL = false;

    protected $response = null;

    /**
     * Конструктор
     *
     * @param  string  $project
     * @param  string  $key
     * @param  string  $useSSL
     * @param  int  $testMode
     */
    public function __construct($project, $key, $useSSL = false, $testMode = false)
    {
        $this->project = $project;
        $this->key = $key;
        $this->useSSL = $useSSL;
        $this->testMode = $testMode;
    }

    /**
     * Отправить SMS
     *
     * @param  string|array  $recipients
     * @param  string  $message
     * @param  string  $sender
     * @return bool|int
     *
     * @deprecated
     */
    public function sendSMS($recipients, $message, $sender, $run_at = null)
    {
        return $this->messageSend($recipients, $message, $sender, $run_at);
    }

    /**
     * Проверить статус доставки сообщений
     *
     * @param  string|array  $messagesId
     * @return bool|array
     *
     * @deprecated
     */
    public function checkStatus($messagesId)
    {
        return $this->messageStatus($messagesId);
    }

    /**
     * Отправить SMS
     *
     * @param  string|array  $recipients
     * @param  string  $message
     * @param  string  $sender
     * @param  string  $run_at
     * @return bool|int
     */
    public function messageSend($recipients, $message, $sender, $run_at = null)
    {
        $params = [
            'recipients' => $recipients,
            'message' => $message,
            'sender' => $sender,
        ];

        if ($run_at != null) {
            $params['run_at'] = $run_at;
        }

        if ($this->testMode) {
            $params['test'] = 1;
        }

        $response = $this->makeRequest('message/send', $params);

        return $response['status'] == self::REQUEST_SUCCESS;
    }

    /**
     * Проверить статус доставки сообщений
     *
     * @param  string|array  $messagesId
     * @return bool|array
     */
    public function messageStatus($messagesId)
    {
        if (! is_array($messagesId)) {
            $messagesId = [$messagesId];
        }

        $response = $this->makeRequest('message/status', [
            'messages_id' => implode(',', $messagesId),
        ]);

        return $response['status'] == self::REQUEST_SUCCESS ? $response['messages'] : false;
    }

    /**
     * Запрос стоимости сообщения
     *
     * @param  string|array  $recipients
     * @param  string  $message
     * @return bool|decimal
     */
    public function messagePrice($recipients, $message)
    {
        $response = $this->makeRequest('message/price', [
            'recipients' => $recipients,
            'message' => $message,
        ]);

        return $response['status'] == self::REQUEST_SUCCESS ? $response['price'] : false;
    }

    /**
     * Запрос информации о номерах
     *
     * @param  string|array  $recipients
     * @return bool|decimal
     */
    public function phoneInfo($numbers)
    {
        $response = $this->makeRequest('message/info', [
            'phones' => $numbers,
        ]);

        return $response['status'] == self::REQUEST_SUCCESS ? $response['info'] : false;
    }

    /**
     * Запросить баланс
     */
    public function userBalance()
    {
        $response = $this->makeRequest('message/balance');

        return $response['status'] == self::REQUEST_SUCCESS ? $response['balance'] : false;
    }

    /**
     * Запросить баланс
     */
    public function getBalance()
    {
        return $this->userBalance();
    }

    /**
     * Отправить запрос
     *
     * @param  string  $function
     * @return stdClass
     */
    protected function makeRequest($function, array $params = [])
    {
        $params = $this->joinArrayValues($params);
        $sign = $this->generateSign($params);
        $params = array_merge(['project' => $this->project], $params);

        $url = ($this->useSSL ? 'https://' : 'http://') . $this->url . '/' . $function;
        $post = http_build_query(array_merge($params, ['sign' => $sign]), '', '&');

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            if ($this->useSSL) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            }
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $response = curl_exec($ch);
            curl_close($ch);
        } else {
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'content' => $post,
                    'timeout' => 10,
                ],
            ]);
            $response = file_get_contents($url, false, $context);
        }

        return $this->response = json_decode($response, true);
    }

    /**
     * Установить адрес шлюза
     *
     * @param  string  $url
     * @return void
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Получить адрес сервера
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    protected function joinArrayValues($params)
    {
        $result = [];
        foreach ($params as $name => $value) {
            $result[$name] = is_array($value) ? implode(',', $value) : $value;
        }

        return $result;
    }

    /**
     * Возвращает ответ сервера последнего запроса
     *
     * @return array
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Сгенерировать подпись
     *
     * @return string
     */
    protected function generateSign(array $params)
    {
        $params['project'] = $this->project;
        ksort($params);

        return md5(sha1(implode(';', array_merge($params, [$this->key]))));
    }
}

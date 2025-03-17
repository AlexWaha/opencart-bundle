<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 *
 * @link    https://alexwaha.com
 *
 * @email   support@alexwaha.com
 *
 * @license GPLv3
 */

namespace Alexwaha\Gateway;

class Smscua
{
    private $gateName = '(SMSC.ua)';

    private $baseUrl = 'https://smsc.ua/sys/send.php';

    private $data;

    private $log;

    public function __construct(array $data, \Log $log)
    {
        $this->data = $data;
        $this->log = $log;
    }

    public function __get($name)
    {
        return $this->data[$name] ?? null;
    }

    public function send()
    {
        if (! $this->username || ! $this->password) {
            $this->log->write($this->gateName . ' Error: Authentication credentials are missing.');

            return false;
        }

        if (! $this->to) {
            $this->log->write($this->gateName . ' Error: Phone destination not found!');

            return false;
        }

        if (! $this->from) {
            $this->log->write($this->gateName . ' Notice: Default Sender is not set! Please input real Sender');

            return false;
        }

        $this->sendRequest($this->to, $this->message, $this->from);

        if (! empty($this->copy)) {
            $this->sendRequest($this->copy, $this->message, $this->from, true);
        }

        return true;
    }

    private function sendRequest($phones, $message, $sender, $isCopy = false)
    {
        $phones = is_array($phones) ? implode(',', $phones) : $phones;

        $params = [
            'login' => $this->username,
            'psw' => $this->password,
            'phones' => $phones,
            'mes' => $message,
            'sender' => $sender,
            'fmt' => 3,
        ];

        $url = $this->baseUrl . '?' . http_build_query($params);

        $response = json_decode(file_get_contents($url), true);

        if (isset($response['error'])) {
            $this->log->write($this->gateName . ' SMS Error: ' . $response['error'] . ' (' . $response['error_code'] . ')');
        } else {
            $this->log->write($this->gateName . ' SMS sent: ' . $response['cnt'] . ' messages. Cost: ' . $response['cost']);

            if ($isCopy) {
                $this->log->write($this->gateName . ' SMS copies sent.');
            }
        }
    }
}

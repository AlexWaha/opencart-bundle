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

final class Smsru
{
    private $gateName = '(SMS.ru)';

    private $baseurl = 'https://sms.ru/';

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
        if (! $this->username) {
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

        $auth = $this->sendRequest('auth/check');

        $this->log->write($this->gateName . ' Authentication: Status = ' . $auth->status);

        if ($auth->status == 'OK') {
            $balance = $this->sendRequest('my/balance');

            $this->log->write($this->gateName . ' Balance: Status = ' . $balance->status . ' Balance: ' . $balance->balance);

            $numbers = $this->to;

            if ($this->copy) {
                $numbers .= ',' . $this->copy;
            }

            if ($balance->balance) {
                $smsData = [
                    'to' => $numbers,
                    'msg' => $this->message,
                    'from' => $this->from,
                    'time' => 0,
                ];

                $result = $this->sendRequest('sms/send', $smsData);

                if ($result->status == 'OK') {
                    $this->log->write($this->gateName . ' SMS sent: ' . $result->status . ' Balance: ' . $result->balance);
                }
            }
        } else {
            $this->log->write($this->gateName . ' Error: Authentication failed!');
        }

        return true;
    }

    public function sendRequest($method, $params = [])
    {
        $params['api_id'] = $this->username;
        $params['json'] = 1;

        $url = $this->baseurl . $method . '?' . http_build_query($params);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response);
    }
}

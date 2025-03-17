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

final class Prostorsms
{
    private $gateName = '(Prostorsms)';

    private $baseurl = 'http://api.prostor-sms.ru/messages/v2/';

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

        $balance = $this->getBalance();

        if (isset($balance[1])) {
            $this->log->write($this->gateName . ' : Balance: ' . $balance[1]);

            $smsData = [
                'login' => $this->username,
                'password' => $this->password,
                'phone' => $this->to,
                'text' => $this->message,
                'sender' => $this->from,
            ];

            $response = $this->sendSMS($smsData);

            $this->log->write($this->gateName . ' : SmsSend: Response: ' . $response[0] . ' ID: ' . $response[1]);

            $status = $this->getStatus($response[1]);

            $this->log->write($this->gateName . ' : SmsSend: ID: ' . $status[0] . ' Status: ' . $status[1]);

            if ($this->copy) {
                $copies = explode(',', $this->copy);

                foreach ($copies as $copy) {
                    $bulkSmsData = [
                        'login' => $this->username,
                        'password' => $this->password,
                        'phone' => $copy,
                        'text' => $this->message,
                        'sender' => $this->from,
                    ];

                    $response = $this->sendSMS($bulkSmsData);

                    $this->log->write($this->gateName . ' : SmsSend: Response: ' . $response[0] . ' ID: ' . $response[1]);

                    $status = $this->getStatus($response[1]);

                    $this->log->write($this->gateName . ' : SmsSend: ID: ' . $status[0] . ' Status: ' . $status[1]);
                }
            }
        } else {
            $this->log->write($this->gateName . ' : Unable to get balance!: ' . $balance[0]);
        }

        return true;
    }

    public function sendSms($params)
    {
        return $this->sendRequest('send', $params);
    }

    public function getStatus($id)
    {
        return $this->sendRequest('status', ['id' => $id]);
    }

    public function getBalance()
    {
        return $this->sendRequest('balance');
    }

    public function sendRequest($method, $params = [])
    {
        $params['login'] = $this->username;
        $params['password'] = $this->password;

        $url = $this->baseurl . $method . '/?' . http_build_query($params);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($ch);
        curl_close($ch);

        return explode(';', $response);
    }
}

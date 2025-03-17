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

final class Rocketsms
{
    private $gateName = '(Rocketsms)';

    private $baseurl = 'https://api.rocketsms.by/json/';

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

        if ($balance) {
            $this->log->write($this->gateName . ': Balance: ' . $balance);

            $sender = $this->from ?: 'RSMS.BY';

            $smsData = [
                'phone' => $this->to,
                'text' => $this->message,
                'sender' => $sender,
            ];

            $response = $this->sendSMS($smsData);

            $this->log->write($this->gateName . ': ' . print_r($response, true));

            if ($this->copy) {
                $copies = explode(',', $this->copy);

                foreach ($copies as $copy) {
                    $bulkSmsData = [
                        'phone' => $copy,
                        'text' => $this->message,
                        'sender' => $sender,
                    ];

                    $response = $this->sendSMS($bulkSmsData);

                    $this->log->write($this->gateName . ': ' . print_r($response, true));
                }
            }
        } else {
            $this->log->write($this->gateName . ': Unable to get balance!');
        }

        return true;
    }

    public function getBalance()
    {
        return $this->sendRequest('balance');
    }

    public function sendSms($data)
    {
        return $this->sendRequest('send', $data);
    }

    public function sendRequest($method, $params = [])
    {
        $params['username'] = $this->username;
        $params['password'] = $this->password;

        $url = $this->baseurl . $method . '?' . http_build_query($params);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}

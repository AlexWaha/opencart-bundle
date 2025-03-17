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

final class Smsgorod
{
    private $gateName = '(Smsgorod)';

    private $baseurl = 'https://new.smsgorod.ru/';

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

        $balance = $this->getBalance();

        if ($balance['status'] == 'success') {
            $this->log->write($this->gateName . ' Status: ' . $balance['status'] . ' Balance: ' . $balance['data']);

            if ($balance['data']) {
                $sender = $this->from ?: 'VIRTA';

                $smsData[] = [
                    'channel' => 'char',
                    'sender' => $sender,
                    'text' => $this->message,
                    'phone' => $this->to,
                ];

                if ($this->copy) {
                    $numbers = explode(',', $this->copy);

                    foreach ($numbers as $number) {
                        $smsData[] = [
                            'channel' => 'char',
                            'sender' => $sender,
                            'text' => $this->message,
                            'phone' => $number,
                        ];
                    }
                }

                $result = $this->sendSMS($smsData);

                $this->log->write($this->gateName . ' SEND: Status: ' . $result['status']);

                if ($result['data']) {
                    foreach ($result['data'] as $data) {
                        $this->log->write($this->gateName . ' SMS ID: ' . $data['id'] . ' Status: ' . $data['status']);
                    }
                }
            } else {
                $this->log->write($this->gateName . ' : Status: ' . $balance['status'] . ', Balance is: ' . $balance['data']);
            }
        } else {
            $this->log->write($this->gateName . ' : Current Balance is 0 or Authorisation fault');
        }

        return true;
    }

    public function getBalance()
    {
        $params['apiKey'] = $this->username;

        return $this->sendRequest('apiUsers/getUserBalanceInfo?' . http_build_query($params));
    }

    public function sendSms($data)
    {
        return $this->sendRequest('apiSms/create', $data);
    }

    public function sendRequest($method, $params = [])
    {
        if ($params) {
            $smsParams['apiKey'] = $this->username;
            $smsParams['sms'] = $params;
        }

        $url = $this->baseurl . $method;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($smsParams) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($smsParams));
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'accept: application/json',
        ]);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}

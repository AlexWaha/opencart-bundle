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

final class Unisender
{
    private $gateName = '(Unisender)';

    private $baseurl = 'https://api.unisender.com/ru/api/';

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

        $numbers = $this->to;

        if ($this->copy) {
            $numbers .= ',' . $this->copy;
        }

        if ($numbers) {
            $smsData = [
                'phone' => $numbers,
                'sender' => $this->from,
                'text' => $this->message,
            ];

            $result = $this->sendRequest('sendSms', $smsData);

            if ($result) {
                if ($result->error) {
                    $this->log->write($this->gateName . ' Sms send error: ' . $result->error . ' (code: ' . $result->code . ')');
                }
                if ($result->result) {
                    $this->log->write($this->gateName . ' Sms sent: Sms_id: ' . $result->result->sms_id . ' Sms cost: ' . $result->result->price . ' ' . $result->result->currency);
                }
            } else {
                $this->log->write($this->gateName . ' Error: API-server is not responding');
            }
        }

        return true;
    }

    public function sendRequest($method, $params)
    {
        $params['api_key'] = $this->username;
        $params['format'] = 'json';

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

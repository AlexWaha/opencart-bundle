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

final class Smsaero
{
    private $gateName = '(SmsAero)';

    private $baseurl = 'https://gate.smsaero.ru/v2/';

    private $channel = 'DIRECT';

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

        $sender = $this->from ?: 'SMS_60294';

        $auth = $this->sendRequest('auth');

        if (is_array($auth) && $auth['success']) {
            $balance = $this->sendRequest('balance');

            if (is_array($balance) && $balance['success'] && isset($balance['data']['balance'])) {
                $this->log->write($this->gateName . ' : Balance Result: ' . $balance['data']['balance'] . $balance['message']);
            } else {
                $this->log->write($this->gateName . ' : Balance Result - ' . $balance['message']);
            }

            $smsData = [
                'number' => $this->to,
                'sign' => $sender,
                'text' => $this->message,
                'channel' => $this->channel,
            ];

            $result = $this->sendRequest('sms/send/', $smsData);

            if (is_array($result) && $result['success']) {
                $this->log->write($this->gateName . ' : Sms Result: Status:' . $result['data']['extendStatus'] . 'Cost:' . $result['data']['cost']);
            } else {
                $this->log->write($this->gateName . ' : Sms Result: ' . $result['message']);
            }

            if ($this->copy) {
                $copies = explode(',', $this->copy);

                $numbers = [];

                foreach ($copies as $copy) {
                    $numbers[] = $copy;
                }

                $bulkSmsData = [
                    'numbers' => $numbers,
                    'sign' => $sender,
                    'text' => $this->message,
                    'channel' => $this->channel,
                ];

                $result = $this->sendRequest('sms/send/', $bulkSmsData);

                if (is_array($result) && $result['success']) {
                    $this->log->write($this->gateName . ' : Sms Result: Status:' . $result['data']['extendStatus'] . 'Cost:' . $result['data']['cost']);
                } else {
                    $this->log->write($this->gateName . ' : Sms Result: ' . $result['message']);
                }
            }
        } else {
            $this->log->write($this->gateName . ' : Auth Result - ' . $auth['message']);
        }

        return true;
    }

    private function sendRequest($method, $params = [])
    {
        $url = $this->baseurl . $method;

        $options = [
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_URL => $url,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_POSTFIELDS => http_build_query($params),
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_USERPWD => $this->username . ':' . $this->password,
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, $options);

        if (! $result = curl_exec($ch)) {
            $result = curl_error($ch);

            $this->log->write(print_r($result, true));

            return false;
        }

        curl_close($ch);

        return json_decode($result, true);
    }
}

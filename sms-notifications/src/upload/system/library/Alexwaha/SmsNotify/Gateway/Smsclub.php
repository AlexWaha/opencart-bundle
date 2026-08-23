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

namespace Alexwaha\SmsNotify\Gateway;

final class SmsClub
{
    private $gateName = '(Smsclub)';

    private $baseurl = 'https://im.smsclub.mobi/sms/';

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

        $this->getBalance();

        $numbers = $this->to;

        if ($this->copy) {
            $numbers .= ',' . $this->copy;
        }

        $phones = explode(',', $numbers);

        if ($phones) {
            $body = [
                'phone' => $phones,
                'message' => $this->message,
                'src_addr' => $this->from,
            ];

            $result = $this->sendSms($body);

            if (! empty($result['success_request'])) {
                if (! empty($result['success_request']['info']) && is_array($result['success_request']['info'])) {
                    foreach ($result['success_request']['info'] as $key => $info) {
                        $this->log->write($this->gateName . ' Message send: ID: ' . $key . ' phone:' . $info);
                    }
                }

                if (! empty($result['success_request']['add_info']) && is_array($result['success_request']['add_info'])) {
                    foreach ($result['success_request']['add_info'] as $key => $info) {
                        $this->log->write($this->gateName . ' Message Info: ID: ' . $key . ' phone:' . $info);
                    }
                }
            } else {
                $this->log->write($this->gateName . ' Error: ' . json_encode($result));
            }
        }

        return true;
    }

    public function sendSms($data)
    {
        return $this->getResponse('send', $data);
    }

    public function getBalance()
    {
        $result = $this->getResponse('balance');

        if ($result && isset($result['success_request'])) {
            if (! empty($result['success_request']['info']) && is_array($result['success_request']['info'])) {
                foreach ($result['success_request']['info'] as $key => $info) {
                    $this->log->write($this->gateName . ' Balance info: ' . $key . ' : ' . $info);
                }
            }
        }

        return $result;
    }

    private function getResponse($method, $body = [])
    {
        $url = $this->baseurl . $method;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->username,
            'Content-Type: application/json',
        ]);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        if ($body) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
        curl_setopt($ch, CURLOPT_URL, $url);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}

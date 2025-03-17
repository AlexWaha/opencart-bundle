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

final class Smsassistentby
{
    private $gateName = '(Sms-assistent)';

    private $baseurl = 'https://userarea.sms-assistent.by/api/v1/';

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

        $balance = $this->sendRequest('credits/plain');

        $this->log->write($this->gateName . ' Balance: ' . $balance);

        if ($balance > 0 && $balance !== '0.00') {
            $smsData = [
                'sender' => $this->from,
                'recipient' => $this->to,
                'message' => $this->message,
            ];

            $smsId = $this->sendRequest('send_sms/plain', $smsData);

            $status = $this->sendRequest('statuses/plain', ['id' => $smsId]);

            $this->log->write($this->gateName . ' SMS sent - ID: ' . $smsId . ' Status: ' . $status);

            if ($this->copy) {
                $numbers = explode(',', $this->copy);

                foreach ($numbers as $number) {
                    if (strlen($number) < 3) {
                        continue;
                    }

                    $smsData['recipient'] = $number;

                    $smsId = $this->sendRequest('send_sms/plain', $smsData);

                    $status = $this->sendRequest('statuses/plain', ['id' => $smsId]);

                    $this->log->write($this->gateName . ' SMS sent - ID: ' . $smsId . ' Status: ' . $status);

                    sleep(1);
                }
            }
        } else {
            $this->log->write($this->gateName . ' Error #: Check your Balance!');
        }

        return true;
    }

    public function sendRequest($method, $params = [])
    {
        $params['user'] = $this->username;
        $params['password'] = $this->password;

        return file_get_contents($this->baseurl . $method . '?' . http_build_query($params));
    }
}

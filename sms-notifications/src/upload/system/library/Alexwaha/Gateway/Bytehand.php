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

final class Bytehand
{
    private $gateName = '(Bytehand)';

    private $baseurl = 'https://api.bytehand.com/v1/';

    private $data;

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

        $balance = $this->sendRequest('balance');

        $this->log->write($this->gateName . ' Balance: ' . $balance->description);

        if ($balance->description > 0) {
            $smsData = [
                'from' => $this->from,
                'to' => $this->to,
                'text' => $this->message,
            ];

            $response = $this->sendRequest('send', $smsData);

            $this->log->write($this->gateName . ' SMS sent: SMS ID: ' . $response->description);

            if ($this->copy) {
                $numbers = explode(',', $this->copy);

                foreach ($numbers as $number) {
                    if (strlen($number) < 3) {
                        continue;
                    }

                    $smsData['to'] = $number;

                    $response = $this->sendRequest('send', $smsData);

                    $this->log->write($this->gateName . ' SMS sent: SMS ID: ' . $response->description);

                    sleep(1);
                }
            }
        } else {
            $this->log->write($this->gateName . ' Error #:' . $balance->status . 'Check your Balance!');
        }

        return true;
    }

    private function sendRequest($type, $params = [])
    {
        $params['id'] = $this->username;
        $params['key'] = $this->password;

        $response = file_get_contents($this->baseurl . $type . '?' . http_build_query($params));

        return json_decode($response);
    }
}

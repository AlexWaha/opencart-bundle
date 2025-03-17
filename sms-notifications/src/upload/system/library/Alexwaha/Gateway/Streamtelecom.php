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

final class Streamtelecom
{
    private $gateName = '(StreamTelecom)';

    private $baseurl = 'http://gateway.api.sc/get/';

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

        $balance = $this->sendRequest(['balance' => 1]);

        $balance = preg_replace('/[^0-9,.]?/', '', $balance);

        $this->log->write($this->gateName . ' Balance: ' . $balance);

        if ($balance > 0) {
            if ($balance && $this->to) {
                $smsData = [
                    'sadr' => $this->from,
                    'dadr' => $this->to,
                    'text' => $this->message,
                ];

                $result = $this->sendRequest($smsData);

                $this->log->write($this->gateName . ' SMS sent: SMS ID: ' . $result);

                if ($this->copy) {
                    $numbers = explode(',', $this->copy);

                    foreach ($numbers as $number) {
                        if (strlen($number) < 3) {
                            continue;
                        }

                        $smsData['dadr'] = $number;

                        $result = $this->sendRequest($smsData);

                        $this->log->write($this->gateName . ' SMS sent: SMS ID: ' . $result);

                        sleep(1);
                    }
                }

                return $result;
            }
        } else {
            $this->log->write($this->gateName . ' Error #:' . $balance . 'Check your Balance!');
        }

        return true;
    }

    public function sendRequest($params)
    {
        $params['user'] = $this->username;
        $params['pwd'] = $this->password;

        $url = $this->baseurl . http_build_query($params);
        $result = file_get_contents($url);

        return json_decode($result);
    }
}

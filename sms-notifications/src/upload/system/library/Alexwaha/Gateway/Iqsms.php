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

final class Iqsms
{
    private $gateName = '(IQSMS)';

    private $baseurl = 'https://api.iqsms.ru/messages/v2/';

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

        $credentials = [
            'login' => $this->username,
            'password' => $this->password,
        ];

        $balance = $this->sendRequest('balance/?' . http_build_query($credentials));

        if ($balance) {
            $this->log->write($this->gateName . ' : Balance: ' . $balance);

            $smsData = [
                'login' => $this->username,
                'password' => $this->password,
                'phone' => $this->to,
                'text' => $this->message,
                'sender' => $this->from,
            ];

            $result = $this->sendRequest('send/?' . http_build_query($smsData));

            $accepted = preg_replace('/;(.*)/', '', $result);

            if ($accepted == 'accepted') {
                $this->log->write($this->gateName . ' SMS sent, Status: ' . $result);
            } else {
                $this->log->write($this->gateName . ' : Result: ' . $result);
            }

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

                    $result = $this->sendRequest('send/?' . http_build_query($bulkSmsData));

                    $accepted = preg_replace('/;(.*)/', '', $result);

                    if ($accepted == 'accepted') {
                        $this->log->write($this->gateName . ' SMS: ' . $result);
                    } else {
                        $this->log->write($this->gateName . ' : Result: ' . $result);
                    }
                }
            }
        } else {
            $this->log->write($this->gateName . ' : Unable to get balance!');
        }

        return true;
    }

    public function sendRequest($data)
    {
        $url = $this->baseurl . $data;

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

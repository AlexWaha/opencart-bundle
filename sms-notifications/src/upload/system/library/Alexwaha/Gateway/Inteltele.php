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

final class Inteltele
{
    private $gateName = '(Sms.intel-tele.com)';

    private $baseurl = 'http://api.sms.intel-tele.com';

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

        $balance = $this->sendRequest('credit');

        $balance_result = preg_replace('/[^0-9.]/', '', $balance->credit);

        $this->log->write($this->gateName . ' Balance: ' . $balance_result);

        if ($balance_result !== 0) {
            $numbers = $this->to;

            if ($this->copy) {
                $numbers .= ',' . $this->copy;
            }

            if ($numbers) {
                $smsData = [
                    'to' => $numbers,
                    'from' => $this->from,
                    'message' => $this->message,
                ];

                $result = $this->sendRequest('message/send', $smsData);

                if ($result->reply->status == 'OK') {
                    $this->log->write($this->gateName . ' SMS sent - ' . $result->reply);
                }
            }
        } else {
            $this->log->write($this->gateName . ' Error Balance null! ' . $balance);
        }

        return true;
    }

    public function sendRequest($method, $params = [])
    {
        $params['username'] = $this->username;
        $params['api_key'] = $this->password;

        $url = $this->baseurl . $method . '/?' . http_build_query($params);
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

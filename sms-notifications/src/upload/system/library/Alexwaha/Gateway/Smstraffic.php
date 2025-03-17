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

final class Smstraffic
{
    private $gateName = '(Smstraffic)';

    private $baseurl = 'https://api.smstraffic.ru/multi.php';

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

        if (isset($balance['account']) && $balance['account'] > 0) {
            $this->log->write($this->gateName . ' Balance: ' . $balance['account']);

            $sender = $this->from ?: 'smsinfo';

            $smsData = [
                'phones' => $this->to,
                'message' => $this->message,
                'originator' => $sender,
                'rus' => 5,
            ];

            $response = $this->sendRequest($smsData);

            if ($response) {
                $this->log->write($this->gateName . ' Sms sent: Result: ' . $response['result'] . ' Code: ' . $response['code'] . ' Desc: ' . $response['description']);
            }

            if ($this->copy) {
                $copies = explode(',', $this->copy);

                $numbersList = [];

                foreach ($copies as $copy) {
                    $numbersList[] = $copy;
                }

                $numbers = implode(',', $numbersList);

                $bulkSmsData = [
                    'phones' => $numbers,
                    'message' => $this->message,
                    'originator' => $sender,
                    'rus' => 5,
                ];

                $response = $this->sendRequest($bulkSmsData);

                if ($response) {
                    $this->log->write($this->gateName . ' Sms sent: Result: ' . $response['result'] . ' Code: ' . $response['code'] . ' Desc: ' . $response['description']);
                }
            }
        } else {
            $this->log->write($this->gateName . ' Current Balance is ' . $balance['account'] . ', Sms not send!');
        }

        return true;
    }

    public function getBalance()
    {
        return $this->sendRequest('&operation=' . rawurlencode('account'));
    }

    public function sendRequest($params)
    {
        $url = $this->baseurl . '?login=' . rawurlencode($this->username) . '&password=' . rawurlencode($this->password) . $params;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $result = curl_exec($ch);
        curl_close($ch);

        $xml = json_encode(simplexml_load_string($result));

        return json_decode($xml, true);
    }
}

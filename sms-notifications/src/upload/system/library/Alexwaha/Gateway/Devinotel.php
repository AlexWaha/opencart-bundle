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

final class Devinotel
{
    private $gateName = '(Devinotel)';

    private $baseurl = 'https://integrationapi.net/rest/';

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
            $this->log->write($this->gateName . ' Notice: Default Sender is set! Please input real Sender');

            return false;
        }

        $sender = $this->from ?: 'TestMC';

        $credentials = [
            'login' => $this->username,
            'password' => $this->password,
        ];

        $session = $this->Auth($credentials);

        $session_id = str_replace('"', '', $session);

        $balance = $this->getBalance($session_id);

        $balance = round($balance);

        if ($balance) {
            $this->log->write($this->gateName . ' : Balance: ' . $balance);

            $smsData = [
                'sessionId' => $session_id,
                'sourceAddress' => $sender,
                'data' => $this->message,
                'destinationAddress' => $this->to,
            ];

            $result = $this->sendSMS($smsData);

            if ($result) {
                $this->log->write($this->gateName . ' : SMS sent result: ' . $result);
            }

            if ($this->copy) {
                $numbers = [];

                $copies = explode(',', $this->copy);

                foreach ($copies as $copy) {
                    $numbers[] = $copy;
                }

                $bulkSmsData = [
                    'sessionId' => $session_id,
                    'DestinationAddresses' => $numbers,
                    'SourceAddress' => $sender,
                    'Data' => $this->message,
                ];

                $result = $this->sendBulkSMS($bulkSmsData);

                if ($result) {
                    $this->log->write($this->gateName . ' : SMS sent result: ' . $result);
                }
            }
        } else {
            $this->log->write($this->gateName . ' : Authorisation fault/Current Balance is 0, Sms not send');
        }

        return true;
    }

    public function Auth($credentials)
    {
        return $this->getRequest($this->baseurl . 'User/sessionid?' . http_build_query($credentials));
    }

    public function getBalance($session_id)
    {
        return $this->getRequest($this->baseurl . 'User/Balance?sessionId=' . $session_id);
    }

    public function sendSMS($data)
    {
        return $this->postRequest($this->baseurl . 'Sms/Send?', http_build_query($data));
    }

    public function sendBulkSMS($data)
    {
        return $this->postRequest($this->baseurl . 'Sms/SendBulk?', http_build_query($data));
    }

    public function getRequest($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    public function postRequest($url, $data)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $headers[] = 'Content-Length: ' . strlen($data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}

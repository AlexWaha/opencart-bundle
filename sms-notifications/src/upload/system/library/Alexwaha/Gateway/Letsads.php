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

final class LetsAds
{
    private $gateName = '(LetsAds)';

    private $baseurl = 'https://letsads.com/api';

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

        $balance = $this->getBalance($this->username, $this->password);

        if ($balance) {
            $this->log->write($this->gateName . ' Balance: Name: ' . $balance['name'] . ' Desc: ' . $balance['description']);
        }

        if ($balance['name'] !== 'Error' && $balance['description']) {
            $xml = '<?xml version="1.0" encoding="utf-8"?>';
            $xml .= '<request>';
            $xml .= '<auth>';
            $xml .= '<login>' . $this->username . '</login>';
            $xml .= '<password>' . $this->password . '</password>';
            $xml .= '</auth>';
            $xml .= '<message>';
            $xml .= '<from>' . $this->from . '</from>';
            $xml .= '<text>' . $this->message . '</text>';
            $xml .= '<recipient>' . $this->to . '</recipient>';
            if ($this->copy) {
                $numbers = explode(',', $this->copy);

                foreach ($numbers as $number) {
                    $xml .= '<recipient>' . $number . '</recipient>';
                }
            }
            $xml .= '</message>';
            $xml .= '</request>';

            $result = $this->sendRequest($xml);

            if ($result && $result['name'] !== 'Error') {
                if ($result['sms_id'] && ! is_array($result['sms_id'])) {
                    $this->log->write($this->gateName . ' SMS sent: Status: ' . $result['name'] . ' Desc: ' . $result['description'] . ' Sms_id: ' . $result['sms_id']);

                    $status = $this->checkStatus($this->username, $this->password, $result['sms_id']);

                    $this->log->write($this->gateName . ' SMS Send Status: Sms_id: ' . $result['sms_id'] . ' Status: ' . $status['description']);
                }

                if ($result['sms_id'] && is_array($result['sms_id'])) {
                    foreach ($result['sms_id'] as $sms_id) {
                        $this->log->write($this->gateName . ' SMS sent: Status: ' . $result['name'] . ' Desc: ' . $result['description'] . ' Sms_id: ' . $sms_id);

                        $status = $this->checkStatus($this->username, $this->password, $sms_id);

                        $this->log->write($this->gateName . ' SMS Send Status: Sms_id: ' . $sms_id . ' Status: ' . $status['description']);
                    }
                }
            } else {
                $this->log->write($this->gateName . ' SMS sent: Status: ' . $result['name'] . ' Desc: ' . $result['description']);
            }
        } else {
            $this->log->write($this->gateName . ' Error: Invalid Balance!');
        }

        return true;
    }

    private function getBalance($login, $password)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<request><auth><login>' . $login . '</login>';
        $xml .= '<password>' . $password . '</password>';
        $xml .= '</auth><balance /></request>';

        $response = $this->sendRequest($xml);

        return $this->decodeXMl($response);
    }

    private function checkStatus($login, $password, $sms_id)
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>';
        $xml .= '<request>';
        $xml .= '<auth>';
        $xml .= '<login>' . $login . '</login>';
        $xml .= '<password>' . $password . '</password>';
        $xml .= '</auth>';
        $xml .= '<sms_id>' . $sms_id . '</sms_id>';
        $xml .= '</request>';

        $response = $this->sendRequest($xml);

        return $this->decodeXMl($response);
    }

    private function sendRequest($xml)
    {
        $ch = curl_init($this->baseurl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    private function decodeXMl($xml)
    {
        $xmlString = simplexml_load_string($xml);

        return json_decode(json_encode($xmlString), true);
    }
}

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

final class Epochtasms
{
    private $gateName = '(Epochta)';

    private $baseurl = 'http://api.myatompark.com/members/sms/xml.php';

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

        $smsData = [
            'user' => $this->username,
            'pwd' => $this->password,
            'sadr' => $this->from,
            'dadr' => $this->to,
            'text' => $this->message,
        ];

        $result = $this->sendRequest($smsData);

        $this->log->write($this->gateName . ' SMS' . print_r($result, true));

        if ($this->copy) {
            $numbers = explode(',', $this->copy);

            foreach ($numbers as $number) {
                if (strlen($number) < 3) {
                    continue;
                }

                $smsData['dadr'] = $number;

                $result = $this->sendRequest($smsData);

                $this->log->write($this->gateName . ' SMS' . print_r($result, true));

                sleep(1);
            }
        }

        return true;
    }

    private function sendRequest($data = [])
    {
        $xml_send = '<?xml version="1.0" encoding="UTF-8"?>
			<SMS>
			  <operations>
			    <operation>SEND</operation>
			  </operations>
			  <authentification>
			    <username>' . $data['user'] . '</username>
			    <password>' . $data['pwd'] . '</password>
			  </authentification>
			  <message>
			    <sender>' . $data['sadr'] . '</sender>
			    <text>' . $data['text'] . '</text>
			  </message>
			  <numbers>
			    <number messageID="msg11">' . $data['dadr'] . '</number>
			  </numbers>
			</SMS>';

        $curl = curl_init();

        $curl_data = [
            CURLOPT_URL => $this->baseurl,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_POST => true,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 100,
            CURLOPT_POSTFIELDS => ['XML' => $xml_send],
        ];

        curl_setopt_array($curl, $curl_data);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }
}

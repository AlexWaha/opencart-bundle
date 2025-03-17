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

final class Nikitasms
{
    private $gateName = '(Nikita SMS)';

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

        $numbers = $this->to;

        if ($this->copy) {
            $copies = explode(',', $this->copy);

            $numbersList = [];

            foreach ($copies as $copy) {
                $numbersList[] = $copy;
            }

            $numbers .= ',' . implode(',', $numbersList);
        }

        $sender = $this->from ?: 'SMSPRO.KG';

        if ($numbers) {
            $smsData = [
                'login' => $this->username,
                'pwd' => $this->password,
                'id' => token(8),
                'sender' => $sender,
                'phones' => $numbers,
                'text' => $this->message,
                'test' => '1',
            ];
        }

        $result = $this->sendSms($smsData);

        $this->log->write($this->gateName . ' ' . 'ID: ' . $result->id . ' ' . 'Status: ' . $result->status . ' ' . 'Phones: ' . $result->phones . ' ' . 'CountSMS: ' . $result->smscnt);

        return true;
    }

    private function sendSms($data = [])
    {
        $xml_send = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml_send .= '<message>';
        $xml_send .= '<login>' . $data['login'] . '</login>';
        $xml_send .= '<pwd>' . $data['pwd'] . '</pwd>';
        $xml_send .= '<id>' . $data['id'] . '</id>';
        $xml_send .= '<sender>' . $data['sender'] . '</sender>';
        $xml_send .= '<text>' . $data['text'] . '</text>';

        $xml_send .= '<phones>';

        $numbers = explode(',', $data['phones']);

        foreach ($numbers as $number) {
            if ($number) {
                $xml_send .= '<phone>' . $number . '</phone>';
            }
        }

        $xml_send .= '</phones></message>';

        $curl = curl_init();

        $curl_data = [
            CURLOPT_URL => 'https://smspro.nikita.kg/api/message',
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_POST => true,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 100,
            CURLOPT_POSTFIELDS => $xml_send,
        ];

        curl_setopt_array($curl, $curl_data);

        $response = curl_exec($curl);

        curl_close($curl);

        return simplexml_load_string($response);
    }
}

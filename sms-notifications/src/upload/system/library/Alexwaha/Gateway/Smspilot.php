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

final class Smspilot
{
    private $gateName = '(Smspilot.ru)';

    private $baseurl = 'https://smspilot.ru/api.php';

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
        if (! $this->username) {
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
            'to' => $this->to,
            'from' => $this->from,
            'send' => $this->message,
            'format' => 'json',
        ];

        $result = $this->sendRequest($smsData);

        if (! isset($result->error)) {
            $this->log->write($this->gateName . ' Success: SMS sent server_id=' . $result->send[0]->server_id);
        } else {
            $this->log->write($this->gateName . ' Error: ' . $result->description_ru);
        }

        if ($this->copy) {
            $numbers = explode(',', $this->copy);

            foreach ($numbers as $number) {
                $smsData['to'] = $number;

                $result = $this->sendRequest($smsData);

                if (! isset($result->error)) {
                    $this->log->write($this->gateName . ' Success: SMS sent server_id=' . $result->send[0]->server_id);
                } else {
                    $this->log->write('Smspilot.ru Error: ' . $result->description_ru);
                }

                sleep(1);
            }
        }

        return true;
    }

    public function sendRequest($params)
    {
        $params['apikey'] = $this->username;

        $url = $this->baseurl . '?' . http_build_query($params);

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

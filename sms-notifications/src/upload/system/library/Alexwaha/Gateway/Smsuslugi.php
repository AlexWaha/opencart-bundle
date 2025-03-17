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

final class Smsuslugi
{
    private $gateName = '(SMS-uslugi.ru)';

    private $baseurl = 'https://lcab.sms-uslugi.ru/lcabApi/';

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

        $auth = $this->sendRequest('info.php');

        $this->log->write($this->gateName . ' Authentication: Status = ' . $auth->descr);

        if ($auth->code == '1') {
            $this->log->write($this->gateName . ' Balance: Code = ' . $auth->code . ' Balance: ' . $auth->account);

            $numbers = $this->to;

            if ($this->copy) {
                $numbers .= ',' . $this->copy;
            }

            if ($auth->account) {
                $smsData = [
                    'to' => $numbers,
                    'from' => $this->from,
                    'txt' => $this->message,
                    'check' => '0',
                ];

                $result = $this->sendRequest('sendSms.php', $smsData);

                if ($result->code == '1') {
                    $this->log->write($this->gateName . ' SMS sent - Count: ' . $result->colsmsOfSending . ' Cost: ' . $result->priceOfSending);
                }
            }
        }

        return true;
    }

    public function sendRequest($method, $params = [])
    {
        $params['login'] = $this->username;
        $params['password'] = $this->password;

        $result = file_get_contents($this->baseurl . $method . '?' . http_build_query($params));

        return json_decode($result);
    }
}

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

final class Smscab
{
    private $gateName = '(SMSCab)';

    private $baseurl = 'http://my.smscab.ru/sys/soap.php?wsdl';

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

        try {
            $client = new SoapClient($this->baseurl);

            $credentials = [
                'login' => $this->username,
                'psw' => $this->password,
            ];

            $balance = $client->get_balance($credentials);

            $this->log->write($this->gateName . ' Balance: ' . $balance->balanceresult->balance . ' Error: ' . $balance->balanceresult->error);

            $numbers = $this->to;

            if ($this->copy) {
                $numbers .= ',' . $this->copy;
            }

            if ($balance->balanceresult->balance && $numbers) {
                $smsData = [
                    'login' => $this->username,
                    'psw' => $this->password,
                    'phones' => $numbers,
                    'mes' => $this->message,
                    'sender' => $this->from,
                    'time' => 0,
                ];

                $result = $client->send_sms($smsData);

                if ($result->sendresult->cnt) {
                    $this->log->write($this->gateName . ' SMS sent: ' . $result->sendresult->cnt . ' Cost: ' . $result->sendresult->cost);
                }
            }
        } catch (SoapFault $fault) {
            $this->log->write(
                "SOAP Error: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})",
                E_USER_ERROR
            );
        }

        return true;
    }
}

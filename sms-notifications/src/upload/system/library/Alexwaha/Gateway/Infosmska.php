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

final class Infosmska
{
    private $gateName = '(Infosmska)';

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
            $numbers .= ',' . $this->copy;
        }

        $sms_id = $this->sendSMS($this->username, $this->password, $numbers, $this->message, $this->from);

        $this->log->write($this->gateName . ' SMS ID:' . $sms_id);

        return true;
    }

    public function sendSMS($login, $password, $number, $text, $sender)
    {
        $host = 'api.infosmska.ru';
        $fp = fsockopen($host, 80);
        fwrite(
            $fp,
            'GET /interfaces/SendMessages.ashx' . '?login=' . rawurlencode($login) . '&pwd=' . rawurlencode($password) . '&phones=' . rawurlencode($number) . '&message=' . rawurlencode($text) . '&sender=' . rawurlencode($sender) . " HTTP/1.1\r\nHost: $host\r\nConnection: Close\r\n\r\n"
        );
        fwrite($fp, 'Host: ' . $host . "\r\n");
        fwrite($fp, "\n");
        $response = '';
        while (! feof($fp)) {
            $response .= fread($fp, 1);
        }
        fclose($fp);
        [
            $other,
            $responseBody
        ] = explode("\r\n\r\n", $response, 2);
        [
            $other,
            $ids_str
        ] = explode(':', $responseBody, 2);
        [
            $sms_id,
            $other
        ] = explode(';', $ids_str, 2);

        return $sms_id;
    }
}

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

final class Smssending
{
    private $gateName = '(Smssending)';

    private $baseurl = 'http://lcab.sms-sending.ru/API/XML/';

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

        if ($balance->code == '1' && (int) $balance->account > '1') {
            $this->log->write($this->gateName . ' Balance: ' . $balance->account);

            $xml = "<?xml version='1.0' encoding='UTF-8'?>";
            $xml .= '<data>';
            $xml .= '<login>' . $this->username . '</login>';
            $xml .= '<password>' . $this->password . '</password>';
            $xml .= '<source>' . $this->from . '</source>';
            $xml .= '<text>' . $this->message . '</text>';
            $xml .= "<to number='" . $this->to . "'></to>";
            $xml .= '</data>';

            $result = $this->sendSMS($xml);

            $resultCode = $this->getResultCode($result->code);

            $this->log->write($this->gateName . ' Sms sent result: ' . $resultCode);

            if ($result->code == '1') {
                $this->log->write($this->gateName . ' Sms sent desc: ' . $result->descr . ' Sms Count: ' . $result->colsmsOfSending . ' Cost: ' . $result->priceOfSending);

                $status = $this->getStatus($result->smsid);

                if ($status->code) {
                    $this->writeSmsResult($status);
                } else {
                    $this->log->write($this->gateName . ' Sms sent Status: ID: ' . $status->descr);
                }
            }

            if ($this->copy) {
                $numbers = explode(',', $this->copy);

                $xml_multi = "<?xml version='1.0' encoding='UTF-8'?>";
                $xml_multi .= '<data>';
                $xml_multi .= '<login>' . $this->username . '</login>';
                $xml_multi .= '<password>' . $this->password . '</password>';
                $xml_multi .= '<source>' . $this->from . '</source>';
                $xml_multi .= '<text>' . $this->message . '</text>';
                foreach ($numbers as $number) {
                    $xml_multi .= "<to number='" . $number . "'></to>";
                }
                $xml_multi .= '</data>';

                $result = $this->sendSMS($xml_multi);

                if ($result->code) {
                    $this->log->write($this->gateName . ' Sms sent desc: ' . $result->descr . 'Sms Count: ' . $result->colsmsOfSending . ' Cost: ' . $result->priceOfSending);

                    $status = $this->getStatus($result->smsid);

                    if ($status->code) {
                        $this->writeSmsResult($status);
                    } else {
                        $this->log->write($this->gateName . ' Sms sent Status: ID: ' . $status->descr);
                    }
                }
            }
        }

        return true;
    }

    public function sendSms($xml)
    {
        return $this->sendRequest('send.php', $xml);
    }

    public function getStatus($id)
    {
        $xml = "<?xml version='1.0' encoding='UTF-8'?>";
        $xml .= '<data>';
        $xml .= '<login>' . $this->username . '</login>';
        $xml .= '<password>' . $this->password . '</password>';
        $xml .= '<smsid >' . $id . '</smsid>';
        $xml .= '</data>';

        return $this->sendRequest('report.php', $xml);
    }

    public function getBalance()
    {
        $xml = "<?xml version='1.0' encoding='UTF-8'?>";
        $xml .= '<data>';
        $xml .= '<login>' . $this->username . '</login>';
        $xml .= '<password>' . $this->password . '</password>';
        $xml .= '</data>';

        return $this->sendRequest('balance.php', $xml);
    }

    private function writeSmsResult($data)
    {
        if ($data->detail->delivered && isset($data->detail->delivered->number)) {
            $this->log->write($this->gateName . ' SmsSend Delivered For: ' . $data->detail->delivered->number);
        }
        if ($data->detail->notDelivered && isset($data->detail->notDelivered->number)) {
            $this->log->write($this->gateName . ' SmsSend notDelivered For: ' . $data->detail->notDelivered->number);
        }
        if ($data->detail->waiting && isset($data->detail->waiting->number)) {
            $this->log->write($this->gateName . ' SmsSend Waiting For: ' . $data->detail->waiting->number);
        }
        if ($data->detail->enqueued && isset($data->detail->enqueued->number)) {
            $this->log->write($this->gateName . ' SmsSend Enqueued For: ' . $data->detail->enqueued->number);
        }
        if ($data->detail->cancel && isset($data->detail->cancel->number)) {
            $this->log->write($this->gateName . ' SmsSend Cancel For: ' . $data->detail->cancel->number);
        }
        if ($data->detail->onModer && isset($data->detail->onModer->number)) {
            $this->log->write($this->gateName . ' SmsSend Enqueued For: ' . $data->detail->onModer->number);
        }
    }

    private function getResultCode($data)
    {
        $codes = [
            '1' => 'Успешно завершенная операция',
            '500' => 'Недостаточно переданных параметров',
            '501' => 'Неверная пара логин/пароль',
            '502' => 'Превышен размер smsid. Максимальный размер 21 символ',
            '503' => 'Неверный формат datetime. Верный: yyyy-mm-dd HH:MM:SS',
            '504' => 'Недопустимое значение Адреса отправителя',
            '520' => 'Получатели смс отсутствуют',
            '70' => 'Ошибка парсера XML документа (х – цифры 0..9)',
        ];

        $code_error = substr($data, '0', '2');

        if ($code_error == '70') {
            $data = '70';
        }

        return $codes[$data];
    }

    private function decodeResult($data)
    {
        $json = json_encode(simplexml_load_string($data));

        return json_decode($json);
    }

    private function sendRequest($method, $xml)
    {
        $url = $this->baseurl . $method;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $result = curl_exec($ch);
        curl_close($ch);

        return $this->decodeResult($result);
    }
}

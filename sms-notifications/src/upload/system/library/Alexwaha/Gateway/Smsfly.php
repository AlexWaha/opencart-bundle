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

final class Smsfly
{
    private $gateName = '(SmsFly)';

    private $baseurl = 'http://sms-fly.com/api/api.php';

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
        $start_time = 'AUTO';
        $end_time = 'AUTO';
        $rate = 1;
        $lifetime = 4;

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

        if ($balance) {
            $this->log->write($this->gateName . ' Balance: ' . print_r($balance, true));

            $xmlData = '<?xml version="1.0" encoding="utf-8"?>';
            $xmlData .= '<request>';
            $xmlData .= '<operation>SENDSMS</operation>';
            $xmlData .= '<message start_time="' . $start_time . '" end_time="' . $end_time . '" lifetime="' . $lifetime . '" rate="' . $rate . '" desc="' . $this->from . '" source="' . $this->from . '">';
            $xmlData .= '<body>' . $this->message . '</body>';
            $xmlData .= '<recipient>' . $this->to . '</recipient>';
            $xmlData .= '</message>';
            $xmlData .= '</request>';

            $result = $this->sendSms($xmlData);

            $this->log->write($this->gateName . ' SMS: ' . print_r($result, true));

            if ($this->copy) {
                $numbers = explode(',', $this->copy);

                $xmlData = '<?xml version="1.0" encoding="utf-8"?>';
                $xmlData .= '<request>';
                $xmlData .= '<operation>SENDSMS</operation>';
                $xmlData .= '<message start_time="' . $start_time . '" end_time="' . $end_time . '" lifetime="' . $lifetime . '" rate="' . $rate . '" desc="' . $this->from . '" source="' . $this->from . '">';
                $xmlData .= '<body>' . $this->message . '</body>';
                foreach ($numbers as $number) {
                    $xmlData .= '<recipient>' . $number . '</recipient>';
                }
                $xmlData .= '</message>';
                $xmlData .= '</request>';

                $result = $this->sendSms($xmlData);

                $this->log->write($this->gateName . ' SMS copy: ' . print_r($result, true));
            }
        } else {
            $this->log->write($this->gateName . ' Error: Unable to get balance!');
        }

        return true;
    }

    private function getBalance()
    {
        $xmlData = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
        $xmlData .= '<request>';
        $xmlData .= '<operation>GETBALANCE</operation>';
        $xmlData .= '</request>';

        return $this->sendRequest($xmlData);
    }

    public function sendSms($xmlData)
    {
        return $this->sendRequest($xmlData);
    }

    private function sendRequest($xmlData)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERPWD, $this->username . ':' . $this->password);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $this->baseurl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: text/xml',
            'Accept: text/xml',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}

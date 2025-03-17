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

final class Osonsms
{
    private $gateName = '(Osonsms)';

    private $baseurl = 'http://api.osonsms.com/';

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

        $sender = $this->from ?: 'OsonSMS';

        $dlm = ';';

        $txn_id = uniqid();

        $balance_hash = hash('sha256', $txn_id . $dlm . $this->username . $dlm . $this->password);

        $balance_creds = [
            'login' => $this->username,
            'str_hash' => $balance_hash,
            'txn_id' => $txn_id,
        ];

        $balance_result = $this->getBalance($balance_creds);

        $balance = json_decode($balance_result, true);

        if (! $balance['error'] && isset($balance['msg']['balance'])) {
            $this->log->write($this->gateName . ' : Balance: ' . $balance['msg']['balance']);

            $smsx_id = uniqid();

            $sms_hash = hash('sha256', $smsx_id . $dlm . $this->username . $dlm . $sender . $dlm . $this->to . $dlm . $this->password);

            $smsData = [
                'from' => $sender,
                'phone_number' => $this->to,
                'msg' => $this->message,
                'login' => $this->username,
                'str_hash' => $sms_hash,
                'txn_id' => $smsx_id,
            ];

            $result = $this->sendSMS($smsData);

            if (! $result['error'] && $result['msg']) {
                $this->log->write($this->gateName . ' : SMS sent result: ' . $result['msg']);
            }

            if ($result['error']) {
                $this->log->write($this->gateName . ' : SMS sent error: ' . $result['msg']);
            }

            if ($this->copy) {
                $numbers = explode(',', $this->copy);

                foreach ($numbers as $number) {
                    $smsbulk_id = uniqid();

                    $smsbulk_hash = hash(
                        'sha256',
                        $smsbulk_id . $dlm . $this->username . $dlm . $sender . $dlm . $number . $dlm . $this->password
                    );

                    $bulkSmsData = [
                        'from' => $sender,
                        'phone_number' => $number,
                        'msg' => $this->message,
                        'login' => $this->username,
                        'str_hash' => $smsbulk_hash,
                        'txn_id' => $smsbulk_id,
                    ];

                    $result = $this->sendSMS($bulkSmsData);

                    if (! $result['error'] && $result['msg']) {
                        $this->log->write($this->gateName . ' : SMS Copy send result: ' . $result['msg']);
                    }

                    if ($result['error']) {
                        $this->log->write($this->gateName . ' : SMS Copy send error: ' . $result['msg']);
                    }
                }
            }
        } else {
            $this->log->write($this->gateName . ' : Authorisation fault or Current Balance is overdraft limit, Sms not send');
        }

        return true;
    }

    public function getBalance($data)
    {
        $result = $this->sendRequest($this->baseurl . 'check_balance.php', http_build_query($data), 'GET');

        return json_encode($result);
    }

    public function sendSMS($data)
    {
        return $this->sendRequest($this->baseurl . 'sendsms_v1.php', http_build_query($data), 'GET');
    }

    public function checkSMS($data)
    {
        return $this->sendRequest($this->baseurl . 'query_sm.php', http_build_query($data), 'GET');
    }

    public function sendRequest($url, $data, $method = 'GET')
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "$url?$data");

        if ($method == 'GET') {
            curl_setopt($ch, CURLOPT_URL, "$url?$data");
        } else {
            if ($method == 'POST') {
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ]);

        $result = curl_exec($ch);
        $error = curl_error($ch);

        curl_close($ch);

        $response = [];

        if ($error) {
            $response['error'] = 1;
            $response['msg'] = $error;
        } else {
            $result = json_decode($result);

            if (isset($result->error)) {
                $response['error'] = 1;
                $response['msg'] = 'Error Code: ' . $result->error->code . ' Message: ' . $result->error->msg;
            } else {
                $response['error'] = 0;
                $response['msg'] = $result;
            }
        }

        return $response;
    }
}

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

final class Eskizuz
{
    private $gateName = '(Eskiz.uz)';

    private $baseurl = 'notify.eskiz.uz/api/';

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

        $sender = $this->from ?: 'Eskiz';

        $credentials = [
            'email' => $this->username,
            'password' => $this->password,
        ];

        $token = $this->getToken($credentials);

        if (! $token) {
            $this->log->write($this->gateName . ' Auth Error: Token is not received!');
        }

        if ($token) {
            $user = $this->getUser($token);

            if (isset($user['message'])) {
                $this->log->write($this->gateName . ' Auth: ' . $user['message']);
            }

            if (isset($user['balance']) && $user['balance'] > 0) {
                $this->log->write($this->gateName . ' User Balance: ' . $user['balance']);

                $smsData = [
                    'from' => $sender,
                    'phone' => $this->to,
                    'text' => $this->message,
                ];

                $result = $this->sendSMS($token, $smsData);

                if ($result['status'] !== 'error' && $result['message']) {
                    $this->log->write($this->gateName . ' SMS sent: Id: ' . $result['id'] . '; Response: ' . $result['message']);

                    if ($result['id'] && $result['id'] !== 'msg_id') {
                        $status = $this->statusSMS($token, $result['id']);
                        $this->log->write($this->gateName . ' SMS sent result: Status: ' . $status['status'] . '; Response: ' . print_r(
                            $status['message'],
                            true
                        ));
                    }
                }

                if ($result['status'] == 'error' && $result['message']) {
                    $this->log->write($this->gateName . ' SMS sent error: ' . print_r($result['message'], true));
                }

                if ($this->copy) {
                    $copies = explode(',', $this->copy);

                    foreach ($copies as $copy) {
                        $bulkSmsData = [
                            'from' => $sender,
                            'phone' => $copy,
                            'text' => $this->message,
                        ];

                        $bulk_result = $this->sendSMS($token, $bulkSmsData);

                        if ($bulk_result['status'] !== 'error' && $bulk_result['message']) {
                            $this->log->write($this->gateName . ' SMS sent: Id: ' . $bulk_result['id'] . '; Response: ' . print_r(
                                $bulk_result['message'],
                                true
                            ));

                            if ($bulk_result['id'] && $bulk_result['id'] !== 'msg_id') {
                                $bulk_status = $this->statusSMS($token, $bulk_result['id']);
                                $this->log->write($this->gateName . ' SMS sent result: Status: ' . $bulk_status['status'] . '; Response: ' . print_r(
                                    $bulk_status['message'],
                                    true
                                ));
                            }
                        }

                        if ($bulk_result['status'] == 'error' && $bulk_result['message']) {
                            $this->log->write($this->gateName . ' SMS sent error: ' . print_r($bulk_result['message'], true));
                        }
                    }
                }
            } else {
                $this->log->write($this->gateName . ' : Authorisation fault or Current Balance is overdraft limit, Sms not send');
            }
        }

        return true;
    }

    public function getToken($data)
    {
        $cache_file = DIR_LOGS . 'eskiz_sms.token';

        if (file_exists($cache_file)) {
            $result = false;

            $cache = json_decode(file_get_contents($cache_file), true);

            $config_token = $cache['token'];
            $created_at = $cache['created_at'];
            $current_date = strtotime(date('Y-m-d'));

            $expired_at = strtotime($created_at . '+28 days');

            if ($config_token && $current_date < $expired_at) {
                $result = $config_token;
            }

            if ($config_token && $current_date > $expired_at) {
                $new_token = $this->refreshToken($cache_file);

                $result = $new_token;
            }

            return $result;
        } else {
            $response = $this->sendRequest('', 'token', 'auth/login', $data);

            if (isset($response['message']) && $response['message'] == 'token_generated') {
                $token = $response['data']['token'];

                $data = [
                    'token' => $token,
                    'created_at' => date('Y-m-d'),
                ];

                file_put_contents($cache_file, json_encode($data));

                return $token;
            }
        }

        return false;
    }

    public function refreshToken($cache_file)
    {
        unlink($cache_file);

        $result = $this->sendRequest('', 'refresh', 'auth/refresh');

        if (isset($result['message']) && $result['message'] == 'token_generated') {
            $token = $result['data']['token'];

            $data = [
                'token' => $token,
                'created_at' => date('Y-m-d'),
            ];

            file_put_contents($cache_file, json_encode($data));

            return $token;
        }

        return false;
    }

    public function getUser($token)
    {
        return $this->sendRequest($token, 'user', 'auth/user');
    }

    public function sendSMS($token, $data)
    {
        return $this->sendRequest($token, 'send', 'message/sms/send', $data);
    }

    public function statusSMS($token, $sms_id)
    {
        return $this->sendRequest($token, 'status', 'message/sms/status/' . $sms_id);
    }

    public function sendRequest($token, $method, $params, $data = [])
    {
        $url = $this->baseurl . $params;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        if ($method == 'token') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, [
                'email' => $data['email'],
                'password' => $data['password'],
            ]);
        }

        if ($method == 'send' && $data) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, [
                'mobile_phone' => $data['phone'],
                'message' => $data['text'],
                'from' => $data['from'],
                'user_sms_id' => mt_rand(3, 9) . strtotime(date('Y-m-d')),
            ]);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer $token",
            ]);
        }

        if ($method == 'status') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer $token",
            ]);
        }

        if ($method == 'refresh') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer $token",
            ]);
        }

        if ($method == 'user') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer $token",
            ]);
        }

        $response = curl_exec($ch);

        curl_close($ch);

        return json_decode($response, true);
    }
}

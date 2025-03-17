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

final class Targetsmsru
{
    private $gateName = '(Targetsmsru)';

    private $baseurl = 'https://sms.targetsms.ru/sendsmsjson.php';

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

        $credentials = [
            'security' => [
                'login' => $this->username,
                'password' => $this->password,
            ],
            'type' => 'balance',
        ];

        $balance = $this->sendRequest($credentials);

        if ($balance) {
            $currentBalance = $balance['money']['value'];
            $this->log->write($this->gateName . ' Balance: ' . $currentBalance);

            $numbers = $this->to;

            if ($this->copy) {
                $numbers .= ',' . $this->copy;
            }

            if ($currentBalance > 0) {
                $numbersList = explode(',', $numbers);
                $abonent = [];
                $number_sms = 0;
                $telephones = [];

                foreach ($numbersList as $number) {
                    $number = $this->validatePhone($number);

                    if ($number && ! in_array($number, $telephones)) {
                        $number_sms++;
                        $telephones[] = $number;
                        $abonent[] = [
                            'phone' => $number,
                            'number_sms' => $number_sms,
                        ];
                    } else {
                        $this->log->write($this->gateName . ' Phone number is not valid!');
                    }
                }

                if ($abonent) {
                    $params = [
                        'security' => [
                            'login' => $this->username,
                            'password' => $this->password,
                        ],
                        'type' => 'sms',
                        'message' => [
                            [
                                'type' => 'sms',
                                'name_delivery' => $_SERVER['HTTP_HOST'],
                                'sender' => $this->from,
                                'text' => $this->message,
                                'abonent' => $abonent,
                            ],
                        ],
                    ];

                    $result = $this->sendRequest($params);

                    $this->log->write($this->gateName . ' ' . print_r($result, true));
                }
            } else {
                $this->log->write($this->gateName . ' Warning! Your balance is below zero');
            }
        } else {
            $this->log->write($this->gateName . ' Unable to get balance!');
        }

        return true;
    }

    private function validatePhone($number)
    {
        $number = preg_replace('/\+?\d+,/', '', $number);
        $first_3 = substr($number, 0, 3);
        $first_1 = substr($number, 0, 1);

        $error = 0;

        if (($first_3 == '380') || ($first_3 == '375')) {
            $last = substr($number, 3);
            if (strlen($last) == 9) {
                $number = '+' . $number;
            } else {
                $error = 1;
            }
        } elseif ($first_1 == '7') {
            $last = substr($number, 1);
            if (strlen($last) == 10) {
                $number = '+' . $number;
            } else {
                $error = 1;
            }
        } elseif ($first_1 == '8') {
            $last = substr($number, 1);
            if (strlen($last) == 10) {
                $number = '+7' . $last;
            } else {
                $error = 1;
            }
        } else {
            $error = 1;
        }

        if (! $error) {
            return $number;
        } else {
            return false;
        }
    }

    private function sendRequest($data)
    {
        $param_json = json_encode($data, true);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'charset=utf8',
            'Expect:',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param_json);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_URL, $this->baseurl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}

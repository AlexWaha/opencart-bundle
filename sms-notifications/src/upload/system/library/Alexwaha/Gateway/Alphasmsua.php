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

final class Alphasmsua
{
    private $gateName = '(Alphasmsua)';

    private $baseurl = 'https://alphasms.ua/api/json.php';

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

        $balance = $this->getBalance();

        if ($balance > 0) {
            $sender = $this->from ?: 'SMARTTEST';

            $params = [
                'id' => $this->generateMessageId(),
                'phone' => $this->to,
                'sms_signature' => $sender,
                'sms_message' => $this->message,
            ];

            $messageId = $this->sendSms($params);

            $this->getMessageStatus($messageId);

            if ($this->copy) {
                $copies = explode(',', $this->copy);

                $numbers = [];

                foreach ($copies as $copy) {
                    $numbers[] = $copy;
                }

                foreach ($numbers as $number) {
                    $params['phone'] = $number;

                    $messageId = $this->sendSms($params);

                    $this->getMessageStatus($messageId);
                }
            }
        }

        return true;
    }

    public function getBalance()
    {
        $response = $this->sendRequest('balance');

        if ($response['success'] && ! isset($response['error'])) {
            if (is_array($response['data'])) {
                $data = $response['data'][0]['data'];
                if ($response['data'][0]['success']) {
                    $this->log->write($this->gateName . ': Current Balance is: ' . $data['amount'] . ' Currency: ' . $data['currency']);

                    return $data['amount'];
                }
                if (isset($response['data'][0]['error'])) {
                    $error = $response['data'][0]['error'];
                    $this->log->write('Alphasms.ua) Balance request Error: ' . $error);
                }
            }
        }

        if (isset($response['error'])) {
            $this->log->write($this->gateName . ': Balance request Error: ' . $response['error']);
        }

        return false;
    }

    public function sendSms(array $params)
    {
        $type = 'sms';

        if ($this->viber['status']) {
            $type = 'viber+sms';
            $params = $this->bodyViber($params, $this->viber);
        }

        $response = $this->sendRequest($type, $params);

        if ($response['success'] && ! isset($response['error'])) {
            if (is_array($response['data'])) {
                $data = $response['data'][0]['data'];
                if ($response['data'][0]['success']) {
                    $this->log->write('Alphasms.ua) SMS sent successfully. Message ID: ' . $data['id'] . ' SMS ID: ' . $data['msg_id']);

                    return $data['id'];
                }
                if (isset($response['data'][0]['error'])) {
                    $error = $response['data'][0]['error'];
                    $this->log->write('Alphasms.ua) SMS sending error: Message ID: ' . $data['id'] . ' Error: ' . $error);
                }
            }
        }

        if (isset($response['error'])) {
            $this->log->write($this->gateName . ' Error: ' . $response['error']);

            return false;
        }

        return false;
    }

    public function getMessageStatus(int $messageId)
    {
        $params = [
            'id' => $messageId,
        ];

        $response = $this->sendRequest('status', $params);

        if ($response['success'] && ! isset($response['error'])) {
            if (is_array($response['data'])) {
                if ($response['data'][0]['success']) {
                    $data = $response['data'][0]['data'];
                    $this->log->write($this->gateName . ': SMS id#' . $data['id'] . ' status: ' . $data['status'] . ' type: ' . $data['type']);
                }
                if (isset($response['data'][0]['error'])) {
                    $error = $response['data'][0]['error'];
                    $this->log->write('(Alphasms.ua): Message status request Error: ' . $error);
                }
            }
        }

        if (isset($response['error'])) {
            $this->log->write('(Alphasms.ua): Message status request Error: ' . $response['error']);
        }
    }

    public function bodyViber($params, $viber)
    {
        if ($viber && $viber['status']) {
            $viber_type = 'text';
            $params['viber_signature'] = $viber['sender'];
            $params['viber_message'] = $viber['message'];

            if ($viber['ttl']) {
                $params['viber_lifetime'] = (int) $viber['ttl'];
            }

            if ($viber['image_url']) {
                $viber_type .= '+image';
                $params['viber_image'] = $viber['image_url'];
            }

            if ($viber['action']) {
                $viber_type .= '+link';
                $params['viber_link'] = $viber['action'];
            }

            if ($viber['caption']) {
                $params['viber_button'] = $viber['caption'];
            }

            $params['viber_type'] = $viber_type;
        }

        return $params;
    }

    private function generateMessageId(): string
    {
        $randomPart = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $day = date('d');
        $hour = date('H');
        $seconds = date('s');

        return $randomPart . $day . $hour . $seconds;
    }

    private function sendRequest($type, $params = [])
    {
        $data = [
            'auth' => $this->username,
            'data' => [
                array_merge(['type' => $type], $params),
            ],
        ];

        $ch = curl_init($this->baseurl);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            $this->log->write('(AlphaSMS) cURL error:' . $error);
            curl_close($ch);

            return false;
        }

        curl_close($ch);

        $decodedResponse = json_decode($response, true);

        if ($httpCode !== 200 || ! $decodedResponse) {
            $this->log->write('(AlphaSMS) API error: HTTP' . $httpCode . ' - Response: ' . $response);

            return false;
        }

        return $decodedResponse;
    }
}

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

final class Mainsms
{
    private $gateName = '(Mainsms)';

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
        require_once DIR_SYSTEM . 'library/Alexwaha/Gateway/lib/mainsms.class.php';

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

        $api = new MainSMSLib($this->username, $this->password, true, false);

        $balance = $api->getBalance();

        if ($balance && round($balance) > 2) {
            $this->log->write($this->gateName . ' : Balance: ' . $balance);

            $sender = $this->from ?: 'INFORM';

            $api->sendSMS($this->to, $this->message, $sender);

            $result = $api->getResponse();

            if (is_array($result) && $result['status'] == 'success') {
                $this->log->write($this->gateName . ' : Sms Result: Status: ' . $result['status'] . '; Cost: ' . $result['price'] . '; Balance: ' . $result['balance']);

                if (isset($result['messages_id'])) {
                    foreach ($result['messages_id'] as $message_id) {
                        $sms_status = $api->checkStatus($message_id);
                        $this->log->write($this->gateName . ' : Sms Status: ID: ' . $message_id . '; Status: ' . strtoupper($sms_status[$message_id]));
                    }
                }

                $this->log->write($this->gateName . ' : Sms Result: Status: ' . $result['status'] . '; Cost: ' . $result['price'] . '; Balance: ' . $result['balance']);
            } else {
                $this->log->write($this->gateName . ' : Sms Result: ' . $result['status'] . ' Message: ' . $result['message']);
            }

            if ($this->copy) {
                $copies = explode(',', $this->copy);

                $numbers = [];

                foreach ($copies as $copy) {
                    $numbers[] = $copy;
                }

                $numbers = implode(',', $numbers);

                $api->sendSMS($numbers, $this->message, $sender);

                $bulkSms = $api->getResponse();

                if (is_array($bulkSms) && $bulkSms['status'] == 'success') {
                    $this->log->write($this->gateName . ' : Sms Result: Status: ' . $bulkSms['status'] . '; Cost: ' . $bulkSms['price'] . '; Balance: ' . $bulkSms['balance']);

                    if (is_array($bulkSms['recipients'])) {
                        foreach ($bulkSms['recipients'] as $key => $recipient) {
                            $this->log->write($this->gateName . ' : Sms Result Recipient: [' . $key . '] - ' . $recipient);
                        }
                    }

                    if (isset($bulkSms['messages_id'])) {
                        foreach ($bulkSms['messages_id'] as $message_id) {
                            $status = $api->checkStatus($message_id);
                            $this->log->write($this->gateName . ' : Sms Status: ID:' . $message_id . '; Status: ' . strtoupper($status[$message_id]));
                        }
                    }
                }
            }
        } else {
            $this->log->write($this->gateName . ' SMS sending Error: Current Balance is : ' . $balance);
        }

        return true;
    }
}

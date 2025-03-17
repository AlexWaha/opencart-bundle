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

namespace Alexwaha;

class SmsDispatcher
{
    private $gateway;

    /**
     * @param  string  $gateName
     * @param  array  $data
     * @param  string  $logFileName
     */
    public function __construct(string $gateName, array $data, string $logFileName = 'aw_sms_notify')
    {
        if (! defined('DIR_SMSGATE')) {
            define('DIR_SMSGATE', DIR_SYSTEM . 'library/Alexwaha/Gateway/');
        }

        if (! $gateName) {
            $gateName = 'Testsms';
        }

        $gateWayPath = DIR_SMSGATE . $gateName . '.php';

        if (file_exists($gateWayPath)) {
            require_once $gateWayPath;
        } else {
            trigger_error('Error: Could not load smsgate file ' . $gateName . '!');
            exit();
        }

        $data = $this->cleanPhoneNumber($data);

        $className = '\\Alexwaha\\Gateway\\' . $gateName;

        $log = new \Log($logFileName . '.log');

        $this->gateway = new $className($data, $log);
    }

    /**
     * @return void
     */
    public function send()
    {
        $this->gateway->send();
    }

    /**
     * @param  array  $data
     * @return array
     */
    private function cleanPhoneNumber(array $data): array
    {
        if (! empty($data['copy'])) {
            $data['copy'] = implode(',', array_map([$this, 'prepareNumber'], explode(',', $data['copy'])));
        }

        $data['to'] = $this->prepareNumber($data['to'] ?? '');

        return $data;
    }

    /**
     * @param  string  $number
     * @return string
     */
    private function prepareNumber(string $number): string
    {
        return preg_replace('/\D/', '', $number);
    }
}

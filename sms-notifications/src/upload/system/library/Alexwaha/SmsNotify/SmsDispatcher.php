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

namespace Alexwaha\SmsNotify;

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
        if (! $gateName) {
            $gateName = 'Testsms';
        }

        $className = '\\Alexwaha\\SmsNotify\\Gateway\\' . $gateName;

        if (! class_exists($className)) {
            trigger_error('Error: Could not load smsgate ' . $gateName . '!');
            exit();
        }

        $data = $this->cleanPhoneNumber($data);

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
     * @return string[]
     */
    public static function availableGateways(): array
    {
        $dir = __DIR__ . '/Gateway';
        $names = [];

        foreach (glob($dir . '/*.php') as $file) {
            $names[] = basename($file, '.php');
        }

        sort($names);

        return $names;
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

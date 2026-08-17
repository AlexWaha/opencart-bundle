<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionAwEasyCheckoutAddressFormat extends Controller
{
    private \Alexwaha\EasyCheckout\AddressFormat $addressFormat;

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->addressFormat = new \Alexwaha\EasyCheckout\AddressFormat($registry);
    }

    public function order(&$route, &$args, &$output)
    {
        if (! is_array($output)) {
            return;
        }

        if (array_key_exists('payment_address_format', $output)) {
            $output['payment_address_format'] = $this->addressFormat->expand(
                (string) $output['payment_address_format'],
                $this->normalizeCustomField($output['payment_custom_field'] ?? []),
                (int) ($output['payment_country_id'] ?? 0)
            );
        }

        if (array_key_exists('shipping_address_format', $output)) {
            $output['shipping_address_format'] = $this->addressFormat->expand(
                (string) $output['shipping_address_format'],
                $this->normalizeCustomField($output['shipping_custom_field'] ?? []),
                (int) ($output['shipping_country_id'] ?? 0)
            );
        }
    }

    private function normalizeCustomField($value): array
    {
        return is_array($value) ? $value : [];
    }

    public function address(&$route, &$args, &$output)
    {
        if (! is_array($output) || ! array_key_exists('address_format', $output)) {
            return;
        }

        $output['address_format'] = $this->addressFormat->expand(
            (string) $output['address_format'],
            $this->normalizeCustomField($output['custom_field'] ?? []),
            (int) ($output['country_id'] ?? 0)
        );
    }

    public function addresses(&$route, &$args, &$output)
    {
        if (! is_array($output)) {
            return;
        }

        foreach ($output as &$address) {
            if (! is_array($address) || ! array_key_exists('address_format', $address)) {
                continue;
            }

            $address['address_format'] = $this->addressFormat->expand(
                (string) $address['address_format'],
                $this->normalizeCustomField($address['custom_field'] ?? []),
                (int) ($address['country_id'] ?? 0)
            );
        }
        unset($address);
    }
}

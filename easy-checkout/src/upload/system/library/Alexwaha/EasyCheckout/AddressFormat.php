<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

namespace Alexwaha\EasyCheckout;

final class AddressFormat
{
    private $registry;

    public function __construct($registry)
    {
        $this->registry = $registry;
    }

    public function resolve(array $customField): array
    {
        $ids = $this->normalizeIds(array_keys($customField));

        if (! $ids) {
            return [];
        }

        $db = $this->registry->get('db');
        $languageId = (int) $this->registry->get('config')->get('config_language_id');
        $idList = implode(',', $ids);

        $fields = $db->query("SELECT cf.custom_field_id, cf.location, cf.sort_order, cf.type, cfd.name
            FROM `" . DB_PREFIX . "aw_ec_custom_field` cf
            LEFT JOIN `" . DB_PREFIX . "aw_ec_custom_field_description` cfd
                ON cfd.custom_field_id = cf.custom_field_id AND cfd.language_id = '" . $languageId . "'
            WHERE cf.custom_field_id IN (" . $idList . ")")->rows;

        $result = [];

        foreach ($fields as $field) {
            $id = (int) $field['custom_field_id'];
            $rawValue = $customField[$id] ?? ($customField[(string) $id] ?? '');

            if (in_array($field['type'], ['select', 'radio', 'checkbox'], true)) {
                $value = $this->resolveValueLabels($id, $rawValue, $languageId);
            } else {
                $value = is_array($rawValue) ? implode(', ', $rawValue) : (string) $rawValue;
            }

            $result[$id] = [
                'name' => (string) ($field['name'] ?? ''),
                'value' => $value,
                'location' => (string) $field['location'],
                'sort_order' => (int) $field['sort_order'],
            ];
        }

        return $result;
    }

    public function expand(string $format, array $customField, int $countryId = 0): string
    {
        if ($format === '') {
            $format = $this->defaultFormat($countryId);
        }

        $resolved = $this->resolve($customField);

        return preg_replace_callback('/\{custom_field_(?:id_)?(\d+)\}/', function (array $matches) use ($resolved) {
            $id = (int) $matches[1];
            $field = $resolved[$id] ?? null;

            if (! $field || $field['location'] !== 'address' || $field['value'] === '') {
                return '';
            }

            $value = str_replace(['{', '}'], '', $field['value']);

            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }, $format);
    }

    private function normalizeIds(array $ids): array
    {
        return array_values(array_unique(array_filter(array_map('intval', $ids))));
    }

    private function resolveValueLabels(int $customFieldId, $rawValue, int $languageId): string
    {
        $valueIds = is_array($rawValue) ? $rawValue : [$rawValue];
        $valueIds = $this->normalizeIds($valueIds);

        if (! $valueIds) {
            return '';
        }

        $db = $this->registry->get('db');
        $idList = implode(',', $valueIds);

        $rows = $db->query("SELECT cfv.custom_field_value_id, cfvd.name
            FROM `" . DB_PREFIX . "aw_ec_custom_field_value` cfv
            LEFT JOIN `" . DB_PREFIX . "aw_ec_custom_field_value_description` cfvd
                ON cfvd.custom_field_value_id = cfv.custom_field_value_id AND cfvd.language_id = '" . $languageId . "'
            WHERE cfv.custom_field_id = '" . $customFieldId . "' AND cfv.custom_field_value_id IN (" . $idList . ")
            ORDER BY cfv.sort_order ASC")->rows;

        $labels = [];
        foreach ($rows as $row) {
            $labels[] = (string) ($row['name'] ?? '');
        }

        return implode(', ', $labels);
    }

    private function defaultFormat(int $countryId): string
    {
        $db = $this->registry->get('db');

        if ($countryId > 0) {
            $row = $db->query("SELECT address_format FROM `" . DB_PREFIX . "country` WHERE country_id = '" . $countryId . "'")->row;

            if (! empty($row['address_format'])) {
                return $row['address_format'];
            }
        }

        $configCountryId = (int) $this->registry->get('config')->get('config_country_id');

        if ($configCountryId > 0 && $configCountryId !== $countryId) {
            $row = $db->query("SELECT address_format FROM `" . DB_PREFIX . "country` WHERE country_id = '" . $configCountryId . "'")->row;

            if (! empty($row['address_format'])) {
                return $row['address_format'];
            }
        }

        return '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
    }
}

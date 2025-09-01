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

final class Icon
{
    public function getIcons(): array
    {
        $jsonFile = __DIR__ . '/fixtures/icons.json';
        $json = file_get_contents($jsonFile);
        $data = json_decode($json, true);

        $icons = [];

        foreach ($data as $value) {
            $valueSlug = str_replace('-', '_', $value);
            $icons[$valueSlug] = '<i class="ti ' . $value . '"></i>';
        }

        return $icons;
    }
}

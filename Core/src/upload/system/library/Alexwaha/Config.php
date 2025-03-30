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

final class Config
{
    private $data;

    /**
     * @param  array  $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * @param $key
     * @return string|null
     */
    public function get($key): ?string
    {
        return $this->data[$key] ?? null;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->data;
    }
}

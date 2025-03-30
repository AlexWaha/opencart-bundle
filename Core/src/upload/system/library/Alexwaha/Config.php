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
     * @return mixed|string
     */
    public function get($key)
    {
        return $this->data[$key] ?? '';
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->data;
    }
}

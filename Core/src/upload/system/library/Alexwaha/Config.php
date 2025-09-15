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
    private array $data;
    private array $post;

    public function __construct(array $data = [], array $post = [])
    {
        $this->data = $data;
        $this->post = $post;
    }

    public function get(string $key, $default = null)
    {
        if (array_key_exists($key, $this->post)) {
            return $this->post[$key];
        }

        return $this->data[$key] ?? $default;
    }

    public function all(): array
    {
        return $this->data;
    }
}

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

use Exception;

final class Language
{
    private $default = 'en-gb';

    private $code;

    private $directory;

    private $data = [];

    private $core;

    /**
     * @param $registry
     * @param  string  $code
     */
    public function __construct($registry, string $code = 'en-gb')
    {
        $this->core = new Core($registry);
        $this->code = substr($code, 0, 2);
        $this->directory = $code;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->data[$key] ?? $key;
    }

    /**
     * @param $key
     * @param $value
     * @return void
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * @param $filename
     * @return array
     * @throws Exception
     */
    public function load($filename): array
    {
        $_ = [];

        $langPath = DIR_LANGUAGE . $this->directory . '/' . $this->directory . '.php';
        $fallbackPath = DIR_LANGUAGE . $this->default . '/' . $this->default . '.php';

        if (is_file($langPath)) {
            require $langPath;
        }

        if (! is_file($langPath) && is_file($fallbackPath)) {
            require $fallbackPath;
        }

        $file = DIR_LANGUAGE . $this->directory . '/' . $filename . '.php';
        if (is_file($file)) {
            require $file;
        }

        $fileDefault = DIR_LANGUAGE . $this->default . '/' . $filename . '.php';
        if (! is_file($file) && is_file($fileDefault)) {
            require $fileDefault;
        }

        $this->data = array_merge($_, $this->loadSupport());

        return $this->data;
    }

    /**
     * @return array
     * @throws Exception
     */
    private function loadSupport(): array
    {
        $coreLangFile = __DIR__ . '/lang/' . $this->code . '/support.php';
        $fallbackCoreLangFile = __DIR__ . '/lang/' . substr($this->default, 0, 2) . '/support.php';

        $params = [];

        $renderData = [];

        if (file_exists($coreLangFile)) {
            $renderData = include $coreLangFile;
        }

        if (! file_exists($coreLangFile) && file_exists($fallbackCoreLangFile)) {
            $renderData = include $fallbackCoreLangFile;
        }

        $template = __DIR__ . '/views/support.twig';

        if (is_file($template)) {
            $content = file_get_contents($template);
            $render = $this->core->render($content, $renderData, true);
            $params['text_aw_support'] = $render;
        }

        return $params;
    }
}

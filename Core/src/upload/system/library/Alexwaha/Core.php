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
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class Core
{
    private $registry;

    public $model;
    public $view;

    /**
     * @param $registry
     */
    public function __construct($registry)
    {
        $this->registry = $registry;
        $this->model = new Model($registry);
        $this->view = new View();
    }

    /**
     * @return mixed
     */
    public function __get($key)
    {
        return $this->registry->get($key);
    }

    /**
     * @param  string  $template
     * @param  array  $data
     * @param  bool  $isString
     * @return string
     * @throws Exception
     */
    public function render(string $template, array $data = [], bool $isString = false): string
    {
        return $this->view->render($template, $data, $isString);
    }

    /**
     * @return int
     */
    public function isLegacy(): int
    {
        if (is_file(DIR_SYSTEM . 'engine/router.php')) {
            return false;
        } elseif (is_file(DIR_SYSTEM . 'framework.php')) {
            return true;
        }
    }

    /**
     * @return array
     */
    public function getToken(): array
    {
        $session = $this->registry->get('session');

        if ($this->isLegacy()) {
            $token = $session->data['token'];
            $tokenData = [
                'param' => 'token=' . $token,
                'token' => $token,
            ];
        } else {
            $token = $session->data['user_token'];
            $tokenData = [
                'param' => 'user_token=' . $token,
                'token' => $token,
            ];
        }

        return $tokenData;
    }

    /**
     * @return Language
     */
    public function getLanguage(): Language
    {
        $code = $this->model->getLanguageCode();

        return new Language($this->registry, $code);
    }

    /**
     * @return string
     */
    public function cyrillicToLatin($string)
    {
        $scheme = [
            'а' => 'a',
            'б' => 'b',
            'в' => 'v',
            'г' => 'g',
            'д' => 'd',
            'е' => 'e',
            'ё' => 'yo',
            'ж' => 'zh',
            'з' => 'z',
            'и' => 'i',
            'й' => 'y',
            'к' => 'k',
            'л' => 'l',
            'м' => 'm',
            'н' => 'n',
            'о' => 'o',
            'п' => 'p',
            'р' => 'r',
            'с' => 's',
            'т' => 't',
            'у' => 'u',
            'ф' => 'f',
            'х' => 'kh',
            'ц' => 'ts',
            'ч' => 'ch',
            'ш' => 'sh',
            'щ' => 'shch',
            'ъ' => '',
            'ы' => 'y',
            'ь' => '',
            'э' => 'e',
            'ю' => 'yu',
            'я' => 'ya',
            'А' => 'A',
            'Б' => 'B',
            'В' => 'V',
            'Г' => 'G',
            'Д' => 'D',
            'Е' => 'E',
            'Ё' => 'Yo',
            'Ж' => 'Zh',
            'З' => 'Z',
            'И' => 'I',
            'Й' => 'Y',
            'К' => 'K',
            'Л' => 'L',
            'М' => 'M',
            'Н' => 'N',
            'О' => 'O',
            'П' => 'P',
            'Р' => 'R',
            'С' => 'S',
            'Т' => 'T',
            'У' => 'U',
            'Ф' => 'F',
            'Х' => 'Kh',
            'Ц' => 'Ts',
            'Ч' => 'Ch',
            'Ш' => 'Sh',
            'Щ' => 'Shch',
            'Ъ' => '',
            'Ы' => 'Y',
            'Ь' => '',
            'Э' => 'E',
            'Ю' => 'Yu',
            'Я' => 'Ya',
        ];

        return strtr($string, $scheme);
    }

    /**
     * @param  string  $number
     * @return string
     */
    public function prepareNumber(string $number): string
    {
        return preg_replace('/\D/', '', $number);
    }

    /**
     * @param  string  $code
     * @param  array  $data
     * @param  int  $moduleId
     * @return void
     */
    public function saveModule(string $code, array $data, int $moduleId = 0): void
    {
        $this->model->saveModule($code, $data, $moduleId);
    }

    /**
     * @param $code
     * @return Config
     */
    public function getConfig($code)
    {
        $this->model->createTables();

        $result = $this->model->getConfig($code);

        $decoded = json_decode($result, true);

        return new Config(is_array($decoded) ? $decoded : []);
    }

    /**
     * @param  string  $code
     * @param  array  $data
     * @return void
     */
    public function setConfig(string $code, array $data)
    {
        $this->model->setConfig($code, json_encode($data));
    }

    /**
     * @param  string  $path
     * @param  bool  $force
     * @return void
     * @throws Exception
     */
    protected function removeByPath(string $path, bool $force = false): void
    {
        if (! $force) {
            throw new Exception("Dangerous deletion method: you must set force=true to delete '{$path}'.");
        }

        if (is_file($path)) {
            unlink($path);
        } elseif (is_dir($path)) {
            $this->recursiveDelete($path);
            rmdir($path);
        }
    }

    /**
     * @param $path
     * @return void
     */
    protected function recursiveDelete($path): void
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
            $path,
            RecursiveDirectoryIterator::SKIP_DOTS
        ), RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isDir()) {
                rmdir($fileinfo->getRealPath());
            } else {
                unlink($fileinfo->getRealPath());
            }
        }
    }

    /**
     * @param  array  $paths
     * @return void
     * @throws Exception
     */
    public function removeLegacyFiles(array $paths = []): void
    {
        if ($paths) {
            foreach ($paths as $path) {
                $this->removeByPath($path, true);
            }
        }
    }
}

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
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Registry;
use Request;

final class Core
{
    private Registry $registry;

    public Model $model;

    public View $view;

    public Request $request;

    public string $robots;

    public function __construct($registry)
    {
        $this->registry = $registry;
        $this->model = new Model($registry);
        $this->view = new View($registry);
        $this->model->ensureSchema();
        $this->request = $registry->get('request');
    }

    /**
     * @return mixed
     */
    public function __get($key)
    {
        return $this->registry->get($key);
    }

    /**
     * @throws Exception
     */
    public function render(string $template, array $data = [], bool $isString = false): string
    {
        return $this->view->render($template, $data, $isString);
    }

    public function addStyles(): void
    {
        $document = $this->registry->get('document');

        if ($document) {
            $document->addStyle('view/stylesheet/awcore/ui.min.css');
        }
    }


    public function isLegacy(): int
    {
        if (is_file(DIR_SYSTEM . 'engine/router.php')) {
            return false;
        } elseif (is_file(DIR_SYSTEM . 'framework.php')) {
            return true;
        }
    }

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

    public function getLanguage(): Language
    {
        $code = $this->model->getLanguageCode();

        return new Language($this->registry, $code);
    }

    public function setRobots($robots): void
    {
        $this->robots = $robots;
    }

    public function getRobots()
    {
        return $this->robots;
    }

    public function getIconsList(): array
    {
        return (new Icon())->getIcons();
    }

    public function getSeoUrls(string $entityQuery, int $entityId = 0)
    {
        return $this->model->getSeoUrls($entityQuery, $entityId, $this->isLegacy());
    }

    public function setSeoUrls(array $seoUrls, string $entityQuery, int $entityId = 0)
    {
        $this->model->setSeoUrls($seoUrls, $entityQuery, $entityId, $this->isLegacy());
    }

    public function seoUrlExists(
        string $seoUrl,
        int $storeId,
        int $languageId,
        string $entityQuery,
        int $entityId = 0
    ) {
        return $this->model->seoUrlExists($seoUrl, $storeId, $languageId, $entityQuery, $entityId, $this->isLegacy());
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

    public function prepareNumber(string $number): string
    {
        return preg_replace('/\D/', '', $number);
    }

    public function setModule(string $code, array $data, int $moduleId = 0): void
    {
        $this->model->setModule($code, $data, $moduleId);
    }

    public function getModule(int $moduleId): array
    {
        return $this->model->getModule($moduleId);
    }

    public function getConfig($code): Config
    {
        $result = $this->model->getConfig($code);
        $data = $result ? json_decode($result, true) : [];

        return new Config($data, $this->request->post ?? []);
    }

    /**
     * @return void
     */
    public function setConfig(string $code, array $data)
    {
        $this->model->setConfig($code, json_encode($data));
    }

    /**
     * @return void
     */
    public function removeConfig(string $code)
    {
        $this->model->removeConfig($code);
    }

    /**
     * Export config as JSON
     *
     * @return string JSON encoded config
     */
    public function exportConfig(string $moduleName): string
    {
        $config = $this->getConfig($moduleName);
        $data = $config->all();

        $exportData = [
            'module' => $moduleName,
            'export_date' => date('Y-m-d H:i:s'),
            'data' => $data,
        ];

        return json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Import config from JSON
     *
     * @param  string|array  $data  JSON string or array
     *
     * @throws Exception
     */
    public function importConfig(string $moduleName, $data): bool
    {
        if (is_string($data)) {
            $importData = json_decode($data, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON format: ' . json_last_error_msg());
            }
        } else {
            $importData = $data;
        }

        if (! isset($importData['data']) || ! is_array($importData['data'])) {
            throw new Exception('Invalid import data format');
        }

        $this->setConfig($moduleName, $importData['data']);

        return true;
    }

    /**
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

    protected function recursiveDelete($path): void
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
            $path,
            FilesystemIterator::SKIP_DOTS
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

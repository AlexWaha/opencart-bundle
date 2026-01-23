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

use Config;
use Exception;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\DebugExtension;
use Twig\Loader\ArrayLoader;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;

final class View
{
    private Config $config;

    private $registry;

    public function __construct($registry)
    {
        $this->registry = $registry;
        $this->config = $registry->get('config');
        require_once __DIR__ . '/vendor/autoload.php';
    }

    /**
     * @throws Exception
     */
    public function render(string $template, array $data = [], bool $isString = false): string
    {
        if ($this->registry->has('language')) {
            foreach ($this->registry->get('language')->all() as $key => $value) {
                if (!isset($data[$key])) {
                    $data[$key] = $value;
                }
            }
        }

        $isAdmin = defined('DIR_CATALOG');

        $config = [
            'autoescape' => false,
            'debug' => true,
            'auto_reload' => true,
            'cache' => DIR_CACHE . 'template/',
        ];

        try {
            if ($isString) {
                $loader = new ArrayLoader(['template' => $template]);
                $twig = new Environment($loader, $config);
                $twig->addExtension(new DebugExtension());

                return $twig->render('template', $data);
            }

            $templateFile = $template . '.twig';

            if ($isAdmin) {
                $baseDir = DIR_TEMPLATE;
            } else {
                $theme = $this->config->get('config_theme') === 'theme_default'
                    ? $this->config->get('theme_default_directory')
                    : $this->config->get('config_theme');

                $baseDir = DIR_TEMPLATE . $theme . '/template/';
            }

            $fullPath = $baseDir . $templateFile;

            // Check OCMOD modification folder first
            $modificationDir = defined('DIR_MODIFICATION') ? DIR_MODIFICATION : DIR_STORAGE . 'modification/';
            $modificationBaseDir = $isAdmin
                ? $modificationDir . 'admin/view/template/'
                : $modificationDir . 'catalog/view/theme/' . ($theme ?? 'default') . '/template/';
            $modificationPath = $modificationBaseDir . $templateFile;

            $originalBaseDir = $baseDir;

            if (is_file($modificationPath)) {
                $fullPath = $modificationPath;
            } elseif (! is_file($fullPath)) {
                $fallbackDir = DIR_TEMPLATE . 'default/template/';
                $fallbackPath = $fallbackDir . $templateFile;

                if (is_file($fallbackPath)) {
                    $originalBaseDir = $fallbackDir;
                    $fullPath = $fallbackPath;
                } else {
                    throw new Exception('Template not found: ' . $fullPath);
                }
            }

            $content = file_get_contents($fullPath);

            $loaders = [];

            if (is_dir($modificationBaseDir)) {
                $loaders[] = new FilesystemLoader($modificationBaseDir);
            }

            if (is_dir($originalBaseDir)) {
                $loaders[] = new FilesystemLoader($originalBaseDir);
            }

            $loaders[] = new ArrayLoader(['template' => $content]);

            $loader = new ChainLoader($loaders);

            $twig = new Environment($loader, $config);
            $twig->addExtension(new DebugExtension());

            return $twig->render($templateFile, $data);
        } catch (LoaderError|RuntimeError|SyntaxError $e) {
            throw new Exception('Twig Rendering Error: ' . $e->getMessage());
        }
    }
}

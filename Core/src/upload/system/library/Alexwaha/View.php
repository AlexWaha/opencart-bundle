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

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\ArrayLoader;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;
use Exception;

final class View
{
    private $config;

    public function __construct($registry)
    {
        $this->config = $registry->get('config');
        require_once __DIR__ . '/vendor/autoload.php';
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
        $isAdmin = defined('DIR_CATALOG');

        $config = [
            'autoescape' => false,
            'debug' => false,
            'auto_reload' => true,
            'cache' => DIR_CACHE . 'template/',
        ];

        try {
            if ($isString) {
                $loader = new ArrayLoader(['template' => $template]);
                $twig = new Environment($loader, $config);

                return $twig->render('template', $data);
            }

            if ($isAdmin) {
                $baseDir = DIR_TEMPLATE;
            } else {
                $theme = $this->config->get('config_theme') === 'theme_default'
                    ? $this->config->get('theme_default_directory')
                    : $this->config->get('config_theme');

                $baseDir = DIR_TEMPLATE . $theme . '/template/';
            }

            $templateFile = $template . '.twig';
            $fullPath = $baseDir . $templateFile;

            if (!is_file($fullPath)) {
                throw new Exception('Template not found: ' . $fullPath);
            }

            $content = file_get_contents($fullPath);

            $loader = new ChainLoader([
                new FilesystemLoader($baseDir),
                new ArrayLoader(['template' => $content]),
            ]);

            $twig = new Environment($loader, $config);

            return $twig->render($templateFile, $data);
        } catch (LoaderError|RuntimeError|SyntaxError $e) {
            throw new Exception('Twig Rendering Error: ' . $e->getMessage());
        }
    }
}

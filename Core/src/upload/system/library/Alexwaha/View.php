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
        $templateDirectory = $this->config->get('template_directory');

        $template = $templateDirectory . $template;

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

            $file = DIR_TEMPLATE . $template . '.twig';

            if (! file_exists($file)) {
                throw new Exception('Template not found: ' . $file);
            }

            $content = file_get_contents($file);

            $loader = new ChainLoader([
                new FilesystemLoader(DIR_TEMPLATE),
                new ArrayLoader(['template' => $content]),
            ]);

            $twig = new Environment($loader, $config);

            return $twig->render($template . '.twig', $data);
        } catch (LoaderError|RuntimeError|SyntaxError $e) {
            throw new Exception('Twig Rendering Error: ' . $e->getMessage());
        }
    }
}

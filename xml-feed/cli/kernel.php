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
require_once __DIR__ . '/../config.php';

require_once DIR_SYSTEM . 'startup.php';

use Alexwaha\Autoload;
use Alexwaha\Init;

$autoloadFile = DIR_SYSTEM . 'library/Alexwaha/Autoload.php';
if (is_file($autoloadFile)) {
    require_once $autoloadFile;
    new Autoload();
}

class Kernel
{
    protected Registry $registry;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->initOc();
        $this->initCore();
    }

    /**
     * @throws Exception
     */
    private function initOc()
    {
        $this->registry = new Registry();

        $loader = new Loader($this->registry);
        $this->registry->set('load', $loader);

        $db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
        $this->registry->set('db', $db);

        $config = new Config();
        $config->load('default');
        $config->load('catalog');

        if (! $config->get('config_theme')) {
            $config->set('config_theme', 'default');
        }

        $this->registry->set('config', $config);

        $request = new Request();
        $this->registry->set('request', $request);

        $response = new Response();
        $response->addHeader('Content-Type: text/html; charset=utf-8');
        $this->registry->set('response', $response);

        $isLegacy = $this->isLegacy();

        if (php_sapi_name() === 'cli') {
            $session = $this->createCliSession();
        } else {
            if ($isLegacy) {
                $session = new Session($config->get('session_engine') ?: 'file');
            } else {
                $session = new Session($config->get('session_engine') ?: 'file', $this->registry);
            }
        }
        $this->registry->set('session', $session);

        $language = new Language($config->get('language_directory'));
        $language->load($config->get('language_default'));
        $this->registry->set('language', $language);

        $event = new Event($this->registry);
        $this->registry->set('event', $event);

        $templateEngine = $config->get('template_engine');
        if (!$templateEngine) {
            $templateEngine = $isLegacy ? 'php' : 'twig';
        }
        $template = new Template($templateEngine);
        $this->registry->set('template', $template);

        $url = new Url(HTTP_SERVER, $config->get('config_secure') ? HTTPS_SERVER : HTTP_SERVER);
        $this->registry->set('url', $url);

        $theme = $config->get('theme_' . $config->get('config_theme') . '_directory') ?: 'default';
        $config->set('template_directory', $theme . '/template/');
        $config->set('config_template', $theme);
    }

    /**
     * @throws Exception
     */
    private function initCore()
    {
        if (class_exists(Init::class)) {
            new Init($this->registry);
        } else {
            throw new Exception('Class \Alexwaha\Init not found.');
        }
    }

    public function getRegistry(): Registry
    {
        return $this->registry;
    }

    private function isLegacy(): bool
    {
        if (is_file(DIR_SYSTEM . 'engine/router.php')) {
            return false;
        } elseif (is_file(DIR_SYSTEM . 'framework.php')) {
            return true;
        }
        return false;
    }

    private function createCliSession()
    {
        return new class () {
            public $session_id = 'cli-session';
            public $data = [];

            public function start($key = 'default', $value = '')
            {
                return $this->session_id;
            }

            public function getId()
            {
                return $this->session_id;
            }

            public function destroy($key = 'default')
            {
                $this->data = [];
            }
        };
    }
}

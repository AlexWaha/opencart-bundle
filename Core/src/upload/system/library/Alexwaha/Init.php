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

final class Init
{
    /**
     * @throws Exception
     */
    public function __construct($registry)
    {
        if (version_compare(phpversion(), '7.2.5', '<')) {
            throw new Exception('PHP 7.2.5+ Required');
        }

        // Load AwCore's bundled Twig (2.x) once at bootstrap so it is the single
        // consistent Twig for the whole request. The host may also ship Twig (3.x)
        // under storage/vendor; both map the Twig\ namespace and whichever
        // autoloader registers first wins. Loading it lazily (in View) let the two
        // majors mix when the host engine had already started resolving Twig classes.
        $vendorAutoload = __DIR__ . '/vendor/autoload.php';
        if (is_file($vendorAutoload)) {
            require_once $vendorAutoload;
        }

        $awCore = new Core($registry);

        $registry->set('awCore', $awCore);
    }
}

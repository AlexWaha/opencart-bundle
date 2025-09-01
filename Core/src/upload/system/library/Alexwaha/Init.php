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

        $awCore = new Core($registry);

        $registry->set('awCore', $awCore);
    }
}

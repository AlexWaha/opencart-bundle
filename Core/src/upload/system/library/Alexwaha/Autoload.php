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

final class Autoload
{
    public function __construct()
    {
        spl_autoload_register(function ($class) {
            $prefix = 'Alexwaha\\';
            $baseDir = __DIR__ . '/';

            if (substr($class, 0, strlen($prefix)) !== $prefix) {
                return;
            }

            $relativeClass = substr($class, strlen($prefix));
            $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

            if (is_file($file)) {
                require $file;
            }
        });
    }
}

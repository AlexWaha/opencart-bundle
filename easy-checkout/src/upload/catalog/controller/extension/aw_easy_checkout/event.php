<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

use Alexwaha\EasyCheckoutHelper;

class ControllerExtensionAwEasyCheckoutEvent extends Controller
{
    private static bool $initialized = false;

    private static ?EasyCheckoutHelper $helper = null;

    private function getHelper(): EasyCheckoutHelper
    {
        if (self::$helper === null) {
            self::$helper = new EasyCheckoutHelper($this->registry);
        }

        return self::$helper;
    }

    public function index(&$route, &$args, &$output)
    {
        if (! self::$initialized) {
            $this->url->addRewrite($this->getHelper());
            self::$initialized = true;
        }
    }

    public function redirect(&$route, &$args)
    {
        $redirectRoute = $this->getHelper()->shouldRedirect();

        if ($redirectRoute) {
            $this->response->redirect($this->url->link($redirectRoute));
        }
    }
}

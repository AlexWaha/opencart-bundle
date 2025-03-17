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
class ControllerExtensionModuleAwSmsNotify extends Controller
{
    public function order(&$route, &$args)
    {
        if (isset($args[0])) {
            $order_id = $args[0];
        } else {
            $order_id = 0;
        }

        if (isset($args[1])) {
            $order_status_id = $args[1];
        } else {
            $order_status_id = 0;
        }

        if (isset($args[2])) {
            $comment = $args[2];
        } else {
            $comment = '';
        }

        if (isset($args[5])) {
            $sendsms = $args[5];
        } else {
            $sendsms = 0;
        }

        if (isset($args[6])) {
            $admin_order = $args[6];
        } else {
            $admin_order = 0;
        }

        $this->load->model('extension/module/aw_sms_notify');
        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($order_id);

        if ($order_info) {
            if (! $order_info['order_status_id'] && ! $admin_order && ! $sendsms) {
                $this->model_extension_module_aw_sms_notify->sendServiceSms($order_info['order_id']);
            }

            if ($order_status_id && $admin_order && $sendsms) {
                $this->model_extension_module_aw_sms_notify->sendOrderStatusSms($order_id, $order_status_id, $comment, $sendsms);
            } elseif ($order_status_id && ! $admin_order && $this->config->get('sms_notify_force')) {
                $this->model_extension_module_aw_sms_notify->sendOrderStatusSms($order_info['order_id'], $order_status_id, $comment, true);
            }
        }
    }

    public function register(&$route, &$args, &$output) {
        $customer_id = $output ?? 0;

        if (isset($args[0])) {
            $password = $args[0]['password'] ?? '';
        } else {
            $password = '';
        }

        $this->load->model('extension/module/aw_sms_notify');

        $this->model_extension_module_aw_sms_notify->sendRegisterSms($customer_id, $password);
    }

    public function review(&$route, &$args)
    {
        if (isset($args[0])) {
            $product_id = $args[0];
        } else {
            $product_id = 0;
        }

        $this->load->model('extension/module/aw_sms_notify');

        $this->model_extension_module_aw_sms_notify->sendReviewsSms($product_id);
    }
}

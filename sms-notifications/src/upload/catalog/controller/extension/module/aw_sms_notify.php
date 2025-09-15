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
    private string $moduleName = 'aw_sms_notify';

    private \Alexwaha\Config $moduleConfig;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->moduleConfig = $this->awCore->getConfig($this->moduleName);
    }

    public function order(&$route, &$args)
    {
        $order_id = $args[0] ?? 0;

        $order_status_id = $args[1] ?? 0;

        $comment = $args[2] ?? '';

        $sendsms = $args[5] ?? 0;

        $admin_order = $args[6] ?? 0;

        $this->load->model('extension/module/' . $this->moduleName);
        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($order_id);

        if ($order_info) {
            if (! $order_info['order_status_id'] && ! $admin_order && ! $sendsms) {
                $this->model_extension_module_aw_sms_notify->sendServiceSms($order_info['order_id']);
            }

            if ($order_status_id && $admin_order && $sendsms) {
                $this->model_extension_module_aw_sms_notify->sendOrderStatusSms($order_id, $order_status_id, $comment, $sendsms);
            } elseif ($order_status_id && ! $admin_order && $this->moduleConfig->get('sms_notify_force')) {
                $this->model_extension_module_aw_sms_notify->sendOrderStatusSms($order_info['order_id'], $order_status_id, $comment, true);
            }
        }
    }

    public function register(&$route, &$args, &$output)
    {
        $customer_id = $output ?? 0;

        if (isset($args[0])) {
            $password = $args[0]['password'] ?? '';
        } else {
            $password = '';
        }

        $this->load->model('extension/module/' . $this->moduleName);

        $this->model_extension_module_aw_sms_notify->sendRegisterSms($customer_id, $password);
    }

    public function review(&$route, &$args)
    {
        $product_id = $args[0] ?? 0;

        $this->load->model('extension/module/' . $this->moduleName);

        $this->model_extension_module_aw_sms_notify->sendReviewsSms($product_id);
    }
}

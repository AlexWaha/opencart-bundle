<?php
use Alexwaha\SmsDispatcher;

class ModelExtensionModuleAwSmsEvent extends Model {
    private $moduleName = 'aw_sms_notify';

    private $moduleConfig;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->moduleConfig = $this->awCore->getConfig($this->moduleName);
    }

    public function addOrderHistory($order_id, $order_status_id, $message) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$order_status_id . "', notify = '1', comment = '" . $this->db->escape($message) . "', date_added = NOW()");
	}

	public function sendOrderStatusSms($order_id, $order_status_id, $comment = false, $sendsms) {
		$this->load->model('sale/order');
		//SMS send with order status
		if ($this->moduleConfig->get('sms_notify_order_alert') && $this->moduleConfig->get('sms_notify_order_status') && in_array($order_status_id, $this->moduleConfig->get('sms_notify_order_status'))) {

			$order_info = $this->model_sale_order->getOrder($order_id);

			if($order_info['language_id']){
				$language_id = $order_info['language_id'];
			}else{
				$language_id = $this->config->get('config_language_id');
			}

            $phone = $this->awCore->prepareNumber($order_info['telephone']);

            $message = [];

            if ($phone) {
                if (in_array($order_status_id, $this->moduleConfig->get('sms_notify_order_status'))) {
                    $sms_template = $this->moduleConfig->get('sms_notify_status_template');

                    $message['sms'] = $this->prepareMessage($order_info, $sms_template[$order_status_id][$language_id], $order_status_id, $comment);

                    $viber_template = $this->moduleConfig->get('sms_notify_viber_template');

                    $message['viber'] = $this->prepareMessage($order_info, $viber_template[$order_status_id][$language_id], $order_status_id, $comment);
                    $check_customer = $this->customerGroup($order_info);

                    if ($check_customer) {
                        if ($message['sms'] && isset($message['viber'])) {
                            $this->addOrderHistory($order_id, $order_status_id, 'SMS ' . $message['sms'] . PHP_EOL . 'Viber ' . $message['viber']);
                        } else {
                            $this->addOrderHistory($order_id, $order_status_id, 'SMS ' . $message['sms']);
                        }

                        if ($message) {
                            $this->sendMessage($phone, $message);
                        }
                    }
                }
            }
		}
	}

	private function prepareMessage($order_data = array(), $template, $comment = false) {
		$order_status_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int) $order_data['order_status_id'] . "' AND language_id = '" . (int) $order_data['language_id'] . "'");

		if ($order_status_query->num_rows) {
			$order_status = $order_status_query->row['name'];
		} else {
			$order_status = false;
		}

		$shipping_cost = 0;
		$order_total_noship = 0;

		if ($this->config->get('total_shipping_status')) {
			$order_shipping_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int) $order_data['order_id'] . "' AND code = 'shipping'");

			if ($order_shipping_query->num_rows) {
				$shipping_cost = $this->currency->format($order_shipping_query->row['value'], $order_data['currency_code'], $order_data['currency_value']);
				$order_total_noship = $order_data['total'] ? $order_data['total'] - $order_shipping_query->row['value'] : '';
			}
		}

		$query_order_product_total = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int) $order_data['order_id'] . "'");

		$query_order_product = $this->db->query("SELECT name, model, price, quantity FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int) $order_data['order_id'] . "'");

        $products = [];

        foreach ($query_order_product->rows as $product) {
            $products[] = [
                'name'     => strip_tags(html_entity_decode($product['name'], ENT_QUOTES, 'UTF-8')),
                'model'    => $product['model'],
                'price'    => $this->currency->format($product['price'], $order_data['currency_code'], $order_data['currency_value']),
                'quantity' => $product['quantity']
            ];
        }

        $data['order_date'] = $order_data['date_added'] ? $order_data['date_added'] : '';
        $data['current_date'] = date('d.m.Y');
        $data['current_time'] = date('H:i');
        $data['store_name'] = $order_data['store_name'] ? $order_data['store_name'] : $this->config->get('config_name');
        $data['store_url'] = $order_data['store_url'] ? $order_data['store_url'] : HTTP_SERVER;
        $data['firstname'] = $order_data['firstname'] ? $order_data['firstname'] : '';
        $data['lastname']  = $order_data['lastname'] ? $order_data['lastname'] : '';
        $data['order_id']  = $order_data['order_id'] ? $order_data['order_id'] : '';
        $data['order_total'] = $order_data['total'] ? $this->currency->format($order_data['total'], $order_data['currency_code'], $order_data['currency_value']) : '';
        $data['order_total_noship'] = $order_total_noship ? $this->currency->format($order_total_noship, $order_data['currency_code'], $order_data['currency_value']) : '';
        $data['order_phone'] = $order_data['telephone'] ? $order_data['telephone'] : '';
        $data['order_track']  = $order_data['track_no'] ? $order_data['track_no'] : '';
        $data['order_comment']  = $comment;
        $data['order_status'] = $order_status;
        $data['payment_method']  = $order_data['payment_method'] ? $order_data['payment_method'] : '';
        $data['payment_city'] = $order_data['payment_city'] ? $order_data['payment_city'] : '';
        $data['payment_address'] = $order_data['payment_address_1'] ? $order_data['payment_address_1'] : '';
        $data['shipping_method'] = $order_data['shipping_method'] ? $order_data['shipping_method'] : '';
        $data['shipping_cost']  = $shipping_cost;
        $data['shipping_city']  = $order_data['shipping_city'] ? $order_data['shipping_city'] : '';
        $data['shipping_address'] = $order_data['shipping_address_1'];
        $data['product_total']  = $query_order_product_total->row['total'];
        $data['products']  = $products;

        $message = $this->awCore->view($template, $data, true);

        $result = html_entity_decode($message, ENT_QUOTES, 'UTF-8');

        if ($this->moduleConfig->get('sms_notify_translit')) {
            return $this->awCore->cyrillicToLatin($result);
        } else {
            return $result;
        }
	}

	private function customerGroup($order){
		$this->load->model('customer/customer');

        $sms_customer_group = $this->moduleConfig->get('sms_notify_customer_group');
        $config_customer_group_id = $this->config->get('config_customer_group_id');

        $customer = [];

        if($order['customer_id']) {
			$customer = $this->model_customer_customer->getCustomer($order['customer_id']);
		}

        $result = true;

        if ($sms_customer_group) {
            $result = false;

            if ($customer && in_array($customer['customer_group_id'], $sms_customer_group)) {
                $result = true;
            }

            if (!$customer && in_array($config_customer_group_id, $sms_customer_group)) {
                $result = true;
            }
        }

        return $result;
	}

    private function sendMessage($phone, $message, $copy = false) {
        $this->load->model('tool/image');

        $viber_image_src = $this->moduleConfig->get('sms_notify_viber_image');
        $viber_image_width = $this->moduleConfig->get('sms_notify_viber_image_width');
        $viber_image_height = $this->moduleConfig->get('sms_notify_viber_image_height');

        if (is_file(DIR_IMAGE . $viber_image_src)) {
            $viber_image = $this->model_tool_image->resize($viber_image_src, $viber_image_width, $viber_image_height);
        } else {
            $viber_image = false;
        }

        $viber_options = [
            'status'    => $this->moduleConfig->get('sms_notify_viber_alert'),
            'sender'    => $this->moduleConfig->get('sms_notify_viber_sender'),
            'message'   => $message['viber'],
            'ttl'       => $this->moduleConfig->get('sms_notify_viber_ttl'),
            'image_url' => $viber_image ? $viber_image : false,
            'caption'   => $this->moduleConfig->get('sms_notify_viber_caption'),
            'action'    => $this->moduleConfig->get('sms_notify_viber_url'),
        ];

        $options = [
            'to'       => $phone,
            'copy'     => $copy,
            'from'     => $this->moduleConfig->get('sms_notify_from'),
            'username' => $this->moduleConfig->get('sms_notify_gate_username'),
            'password' => $this->moduleConfig->get('sms_notify_gate_password'),
            'message'  => $message['sms'],
            'viber'    => $viber_options,
        ];

        $dispatcher = new SmsDispatcher(
            $this->moduleConfig->get('sms_notify_gatename'),
            $options,
            $this->moduleConfig->get('sms_notify_log_filename')
        );
        $dispatcher->send();
    }
}

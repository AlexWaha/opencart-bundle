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

use Alexwaha\SmsNotify\SmsDispatcher;

class ModelExtensionModuleAwSmsNotify extends Model
{
    private string $moduleName = 'aw_sms_notify';

    private \Alexwaha\Config $moduleConfig;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->moduleConfig = $this->awCore->getConfig($this->moduleName);
    }
    public function send($data)
    {
        if (isset($data['phone']) && $data['phone']) {
            $phone = $this->awCore->prepareNumber($data['phone']);
        } else {
            $phone = false;
        }

        $message = [];

        if (isset($data['message']) && $data['message']) {
            $text = $data['message'];

            $message = [
                'sms' => $text,
                'viber' => $text,
            ];

            if ($this->moduleConfig->get('sms_notify_translit')) {
                $text = $this->awCore->cyrillicToLatin($data['message']);

                $message = [
                    'sms' => $text,
                    'viber' => $text,
                ];
            }
        }

        if ($phone && $message) {
            $this->sendMessage($phone, $message);

            return true;
        } else {
            return false;
        }
    }

    public function sendServiceSms($order_id)
    {
        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($order_id);

        if ($order_info['language_id']) {
            $language_id = $order_info['language_id'];
        } else {
            $language_id = $this->config->get('config_language_id');
        }

        if ($order_info) {
            $phone = $this->awCore->prepareNumber($order_info['telephone']);
            $check_customer = $this->customerGroup($order_info);

            $message = [];
            $sms_payments = $this->moduleConfig->get('sms_notify_payment');
            $client_alert = $this->moduleConfig->get('sms_notify_client_alert');

            if ($sms_payments && in_array($order_info['payment_code'], $sms_payments)) {
                if ($phone) {
                    $payment_template = $this->moduleConfig->get('sms_notify_payment_template');
                    $payment_viber_template = $this->moduleConfig->get('sms_notify_payment_viber_template');

                    if ($check_customer) {
                        $message['sms'] = $this->prepareMessage(
                            $order_info,
                            $payment_template[$order_info['payment_code']][$language_id],
                            '',
                            $order_info['comment']
                        );
                        $message['viber'] = $this->prepareMessage(
                            $order_info,
                            $payment_viber_template[$order_info['payment_code']][$language_id],
                            '',
                            $order_info['comment']
                        );

                        $this->sendMessage($phone, $message);
                    }
                }
            } else {
                if ($client_alert && $phone) {
                    $client_template = $this->moduleConfig->get('sms_notify_client_template');
                    $client_viber_template = $this->moduleConfig->get('sms_notify_client_viber_template');

                    if ($check_customer) {
                        $message['sms'] = $this->prepareMessage(
                            $order_info,
                            $client_template[$language_id],
                            '',
                            $order_info['comment']
                        );
                        $message['viber'] = $this->prepareMessage(
                            $order_info,
                            $client_viber_template[$language_id],
                            '',
                            $order_info['comment']
                        );

                        $this->sendMessage($phone, $message);
                    }
                }
            }

            if ($this->moduleConfig->get('sms_notify_admin_alert')) {
                $phone = $this->awCore->prepareNumber($this->moduleConfig->get('sms_notify_to'));

                $text = $this->prepareMessage(
                    $order_info,
                    $this->moduleConfig->get('sms_notify_admin_template'),
                    '',
                    $order_info['comment']
                );

                $message = [
                    'sms' => $text,
                    'viber' => $text,
                ];

                $this->sendMessage($phone, $message, $this->moduleConfig->get('sms_notify_copy'));
            }

            // Telegram (admin) — parallel channel, fires for new order regardless of SMS settings
            $this->sendTelegram('order', $this->buildOrderTelegramData($order_info));
        }
    }

    public function sendOrderStatusSms($order_id, $order_status_id, $comment)
    {
        $this->load->model('checkout/order');

        if ($this->moduleConfig->get('sms_notify_order_alert') && $this->moduleConfig->get('sms_notify_order_status')) {
            $order_info = $this->model_checkout_order->getOrder($order_id);

            if ($order_info['language_id']) {
                $language_id = $order_info['language_id'];
            } else {
                $language_id = $this->config->get('config_language_id');
            }

            $phone = $this->awCore->prepareNumber($order_info['telephone']);

            $message = [];

            if ($phone) {
                if (in_array($order_status_id, $this->moduleConfig->get('sms_notify_order_status'))) {
                    $sms_template = $this->moduleConfig->get('sms_notify_status_template');

                    $message['sms'] = $this->prepareMessage(
                        $order_info,
                        $sms_template[$order_status_id][$language_id],
                        $order_status_id,
                        $comment
                    );

                    $viber_template = $this->moduleConfig->get('sms_notify_viber_template');

                    $message['viber'] = $this->prepareMessage(
                        $order_info,
                        $viber_template[$order_status_id][$language_id],
                        $order_status_id,
                        $comment
                    );
                    $check_customer = $this->customerGroup($order_info);

                    if ($check_customer) {
                        if ($message['sms'] && isset($message['viber'])) {
                            $this->addOrderHistory(
                                $order_id,
                                $order_status_id,
                                'SMS ' . $message['sms'] . PHP_EOL . 'Viber ' . $message['viber']
                            );
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

    public function sendReviewsSms($product_id, $review_data = [])
    {
        $this->load->model('catalog/product');

        $product_data = $this->model_catalog_product->getProduct($product_id);

        if ($product_data) {
            $template = $this->moduleConfig->get('sms_notify_reviews_template');

            $data['product'] = [
                'name' => utf8_substr(
                    strip_tags(html_entity_decode($product_data['name'], ENT_QUOTES, 'UTF-8')),
                    0,
                    50
                ) . '..',
                'model' => $product_data['model'],
                'sku' => $product_data['sku'],
                'date' => date('d.m.Y H:i'),
            ];

            $data['review'] = [
                'author' => $review_data['name'] ?? '',
                'text' => $review_data['text'] ?? '',
                'rating' => $review_data['rating'] ?? '',
            ];

            $data['author'] = $data['review']['author'];
            $data['firstname'] = $data['review']['author'];
            $data['text'] = $data['review']['text'];
            $data['rating'] = $data['review']['rating'];
            $data['product_name'] = $data['product']['name'];
            $data['product_model'] = $data['product']['model'];
            $data['product_sku'] = $data['product']['sku'];
            $data['date'] = $data['product']['date'];

            $text = $this->awCore->render($template, $data, true);

            if ($this->moduleConfig->get('sms_notify_translit')) {
                $text = $this->awCore->cyrillicToLatin($text);
            }

            $message = [
                'sms' => $text,
                'viber' => $text,
            ];

            if ($this->moduleConfig->get('sms_notify_reviews')) {
                $phone = $this->awCore->prepareNumber($this->moduleConfig->get('sms_notify_to'));

                $this->sendMessage($phone, $message);
            }

            // Telegram (admin) — parallel channel for new review
            $this->sendTelegram('review', $data);
        }
    }

    public function sendRegisterSms($customer_id, $password)
    {
        if ($customer_id) {
            $this->load->model('account/customer');

            $customer = $this->model_account_customer->getCustomer($customer_id);

            $data['register'] = [
                'firstname' => $customer['firstname'],
                'lastname' => $customer['lastname'],
                'email' => $customer['email'],
                'phone' => $customer['telephone'],
                'password' => $password
            ];

            $data['firstname'] = $customer['firstname'];
            $data['lastname'] = $customer['lastname'];
            $data['email'] = $customer['email'];
            $data['phone'] = $customer['telephone'];
            $data['password'] = $password;

            if ($this->moduleConfig->get('sms_notify_register_alert')) {
                $template = $this->moduleConfig->get('sms_notify_register_template');

                $language_id = $this->config->get('config_language_id');

                $text = $this->awCore->render($template[$language_id], $data, true);

                $message = [];

                if ($text) {
                    if ($this->moduleConfig->get('sms_notify_translit')) {
                        $text = $this->awCore->cyrillicToLatin($text);
                    }

                    $message['sms'] = $text;
                    $message['viber'] = $text;
                }

                $phone = $this->awCore->prepareNumber($customer['telephone']);

                $this->sendMessage($phone, $message);
            }

            // Telegram (admin) — parallel channel for new registration
            $this->sendTelegram('register', $data);
        }
    }

    private function prepareMessage($order_data, $template, $order_status_id = false, $comment = false)
    {
        $this->load->model('checkout/order');

        if ($order_status_id) {
            $order_status_query = $this->db->query('SELECT * FROM ' . DB_PREFIX . "order_status WHERE order_status_id = '" . (int) $order_status_id . "' AND language_id = '" . (int) $order_data['language_id'] . "'");
        } else {
            $order_status_query = $this->db->query('SELECT * FROM ' . DB_PREFIX . "order_status WHERE order_status_id = '" . (int) $order_data['order_status_id'] . "' AND language_id = '" . (int) $order_data['language_id'] . "'");
        }

        if ($order_status_query->num_rows) {
            $order_status = $order_status_query->row['name'];
        } else {
            $order_status = false;
        }

        $shipping_cost = 0;
        $order_total_noship = 0;

        if ($this->config->get('total_shipping_status')) {
            $order_shipping_query = $this->db->query('SELECT * FROM ' . DB_PREFIX . "order_total WHERE order_id = '" . (int) $order_data['order_id'] . "' AND code = 'shipping'");

            if ($order_shipping_query->num_rows) {
                $shipping_cost = $this->currency->format(
                    $order_shipping_query->row['value'],
                    $order_data['currency_code'],
                    $order_data['currency_value']
                );
                $order_total_noship = $order_data['total'] ? $order_data['total'] - $order_shipping_query->row['value'] : '';
            } else {
                $order_total_noship = $order_data['total'];
            }
        }

        $query_order_product_total = $this->db->query('SELECT COUNT(*) AS total FROM ' . DB_PREFIX . "order_product WHERE order_id = '" . (int) $order_data['order_id'] . "'");

        $query_order_product = $this->db->query('SELECT name, model, price, quantity FROM ' . DB_PREFIX . "order_product WHERE order_id = '" . (int) $order_data['order_id'] . "'");

        $products = [];

        foreach ($query_order_product->rows as $product) {
            $products[] = [
                'name' => strip_tags(html_entity_decode($product['name'], ENT_QUOTES, 'UTF-8')),
                'model' => $product['model'],
                'price' => $this->currency->format(
                    $product['price'],
                    $order_data['currency_code'],
                    $order_data['currency_value']
                ),
                'quantity' => $product['quantity'],
            ];
        }

        $data['order_date'] = $order_data['date_added'] ?: '';
        $data['current_date'] = date('d.m.Y');
        $data['current_time'] = date('H:i');
        $data['store_name'] = $order_data['store_name'] ?: $this->config->get('config_name');
        $data['store_url'] = $order_data['store_url'] ?: HTTP_SERVER;
        $data['firstname'] = $order_data['firstname'] ?: '';
        $data['lastname'] = $order_data['lastname'] ?: '';
        $data['order_id'] = $order_data['order_id'] ?: '';
        $data['order_total'] = strip_tags($order_data['total'] ? $this->currency->format(
            $order_data['total'],
            $order_data['currency_code'],
            $order_data['currency_value']
        ) : '');
        $data['order_total_noship'] = strip_tags($order_total_noship ? $this->currency->format(
            $order_total_noship,
            $order_data['currency_code'],
            $order_data['currency_value']
        ) : '');
        $data['order_phone'] = $order_data['telephone'] ?: '';
        $data['order_comment'] = $comment;
        $data['order_track_no'] = isset($order_data['track_no']) ?: '';
        $data['order_status'] = $order_status;
        $data['payment_method'] = $order_data['payment_method'] ?: '';
        $data['payment_city'] = $order_data['payment_city'] ?: '';
        $data['payment_address'] = $order_data['payment_address_1'] ?: '';
        $data['shipping_method'] = $order_data['shipping_method'] ?: '';
        $data['shipping_cost'] = strip_tags($shipping_cost);
        $data['shipping_city'] = $order_data['shipping_city'] ?: '';
        $data['shipping_address'] = $order_data['shipping_address_1'];
        $data['product_total'] = $query_order_product_total->row['total'];
        $data['products'] = $products;

        $message = $this->awCore->render($template, $data, true);

        $result = html_entity_decode($message, ENT_QUOTES, 'UTF-8');

        if ($this->moduleConfig->get('sms_notify_translit')) {
            return $this->awCore->cyrillicToLatin($result);
        } else {
            return $result;
        }
    }

    private function customerGroup($order)
    {
        $this->load->model('account/customer');

        $sms_customer_group = $this->moduleConfig->get('sms_notify_customer_group');
        $config_customer_group_id = $this->config->get('config_customer_group_id');

        $customer = [];

        if ($order['customer_id']) {
            $customer = $this->model_account_customer->getCustomer($order['customer_id']);
        }

        $result = true;

        if ($sms_customer_group) {
            $result = false;

            if ($customer && in_array($customer['customer_group_id'], $sms_customer_group)) {
                $result = true;
            }

            if (! $customer && in_array($config_customer_group_id, $sms_customer_group)) {
                $result = true;
            }
        }

        return $result;
    }

    private function addOrderHistory($order_id, $order_status_id, $message)
    {
        $this->db->query('INSERT INTO ' . DB_PREFIX . "order_history SET order_id = '" . (int) $order_id . "', order_status_id = '" . (int) $order_status_id . "', notify = '1', comment = '" . $this->db->escape($message) . "', date_added = NOW()");
    }

    private function sendMessage($phone, $message, $copy = false)
    {
        $this->load->model('tool/image');

        $viber_image_src = $this->moduleConfig->get('sms_notify_viber_image');
        $viber_image_width = $this->moduleConfig->get('sms_notify_viber_image_width');
        $viber_image_height = $this->moduleConfig->get('sms_notify_viber_image_height');

        if ($viber_image_src && is_file(DIR_IMAGE . $viber_image_src)) {
            $viber_image = $this->model_tool_image->resize($viber_image_src, $viber_image_width, $viber_image_height);
        } else {
            $viber_image = false;
        }

        $viber_options = [
            'status' => $this->moduleConfig->get('sms_notify_viber_alert'),
            'sender' => $this->moduleConfig->get('sms_notify_viber_sender'),
            'message' => $message['viber'],
            'ttl' => $this->moduleConfig->get('sms_notify_viber_ttl'),
            'image_url' => $viber_image ?: false,
            'caption' => $this->moduleConfig->get('sms_notify_viber_caption'),
            'action' => $this->moduleConfig->get('sms_notify_viber_url'),
        ];

        $options = [
            'to' => $phone,
            'copy' => $copy,
            'from' => $this->moduleConfig->get('sms_notify_from'),
            'username' => $this->moduleConfig->get('sms_notify_gate_username'),
            'password' => $this->moduleConfig->get('sms_notify_gate_password'),
            'message' => $message['sms'],
            'viber' => $viber_options,
        ];

        $dispatcher = new SmsDispatcher(
            $this->moduleConfig->get('sms_notify_gatename'),
            $options,
            $this->moduleConfig->get('sms_notify_log_filename')
        );
        $dispatcher->send();
    }

    /**
     * Build a Telegram payload for the order context (mirrors prepareMessage data shape).
     *
     * @param  array $order_info
     * @return array
     */
    private function buildOrderTelegramData(array $order_info): array
    {
        $shipping_cost = 0;
        $order_total_noship = $order_info['total'] ?? 0;

        if ($this->config->get('total_shipping_status')) {
            $order_shipping_query = $this->db->query('SELECT * FROM ' . DB_PREFIX . "order_total WHERE order_id = '" . (int) $order_info['order_id'] . "' AND code = 'shipping'");

            if ($order_shipping_query->num_rows) {
                $shipping_cost = $this->currency->format(
                    $order_shipping_query->row['value'],
                    $order_info['currency_code'],
                    $order_info['currency_value']
                );
                $order_total_noship = $order_info['total']
                    ? $order_info['total'] - $order_shipping_query->row['value']
                    : '';
            }
        }

        $query_order_product_total = $this->db->query('SELECT COUNT(*) AS total FROM ' . DB_PREFIX . "order_product WHERE order_id = '" . (int) $order_info['order_id'] . "'");
        $query_order_product = $this->db->query('SELECT name, model, price, quantity FROM ' . DB_PREFIX . "order_product WHERE order_id = '" . (int) $order_info['order_id'] . "'");

        $products = [];

        foreach ($query_order_product->rows as $product) {
            $products[] = [
                'name' => strip_tags(html_entity_decode($product['name'], ENT_QUOTES, 'UTF-8')),
                'model' => $product['model'],
                'price' => $this->currency->format(
                    $product['price'],
                    $order_info['currency_code'],
                    $order_info['currency_value']
                ),
                'quantity' => $product['quantity'],
            ];
        }

        $data = [];
        $data['order'] = [
            'id'              => $order_info['order_id'] ?? '',
            'total'           => strip_tags($order_info['total'] ? $this->currency->format(
                $order_info['total'],
                $order_info['currency_code'],
                $order_info['currency_value']
            ) : ''),
            'total_noship'    => strip_tags($order_total_noship ? $this->currency->format(
                $order_total_noship,
                $order_info['currency_code'],
                $order_info['currency_value']
            ) : ''),
            'phone'           => $order_info['telephone'] ?? '',
            'comment'         => $order_info['comment'] ?? '',
            'date'            => $order_info['date_added'] ?? '',
            'payment_method'  => $order_info['payment_method'] ?? '',
            'shipping_method' => $order_info['shipping_method'] ?? '',
            'shipping_city'   => $order_info['shipping_city'] ?? '',
            'shipping_address' => $order_info['shipping_address_1'] ?? '',
            'shipping_cost'   => strip_tags((string) $shipping_cost),
            'product_total'   => $query_order_product_total->row['total'],
        ];
        $data['customer'] = [
            'firstname' => $order_info['firstname'] ?? '',
            'lastname'  => $order_info['lastname'] ?? '',
            'email'     => $order_info['email'] ?? '',
            'phone'     => $order_info['telephone'] ?? '',
        ];
        $data['products'] = $products;
        $data['store_name'] = $order_info['store_name'] ?? $this->config->get('config_name');
        $data['store_url']  = $order_info['store_url'] ?? HTTP_SERVER;

        // Flat aliases (parity with SMS prepareMessage data shape)
        $data['order_id']         = $data['order']['id'];
        $data['order_total']      = $data['order']['total'];
        $data['order_phone']      = $data['order']['phone'];
        $data['order_comment']    = $data['order']['comment'];
        $data['payment_method']   = $data['order']['payment_method'];
        $data['shipping_method']  = $data['order']['shipping_method'];
        $data['shipping_city']    = $data['order']['shipping_city'];
        $data['shipping_address'] = $data['order']['shipping_address'];
        $data['shipping_cost']    = $data['order']['shipping_cost'];
        $data['product_total']    = $data['order']['product_total'];
        $data['firstname']        = $data['customer']['firstname'];
        $data['lastname']         = $data['customer']['lastname'];

        return $data;
    }

    /**
     * Dispatch a Telegram message for the given event if enabled.
     *
     * @param  string $eventKey one of: order, register, review
     * @param  array  $data     template render context
     * @return void
     */
    private function sendTelegram(string $eventKey, array $data): void
    {
        if (!(bool) $this->moduleConfig->get('tg_enabled', false)) {
            return;
        }

        $token = trim((string) $this->moduleConfig->get('tg_bot_token', ''));
        $chatId = trim((string) $this->moduleConfig->get('tg_chat_id', ''));

        if ($token === '' || $chatId === '') {
            return;
        }

        $allowed = ['order', 'register', 'review'];

        if (!in_array($eventKey, $allowed, true)) {
            return;
        }

        if (!(bool) $this->moduleConfig->get('tg_alert_' . $eventKey, false)) {
            return;
        }

        $tplArr = (array) $this->moduleConfig->get('tg_template_' . $eventKey, []);
        $languageId = (int) $this->config->get('config_language_id');
        $template = (string) ($tplArr[$languageId] ?? '');

        if ($template === '') {
            return;
        }

        $template = html_entity_decode($template, ENT_QUOTES, 'UTF-8');

        try {
            $message = $this->awCore->render($template, $data, true);
        } catch (\Throwable $e) {
            $this->writeTelegramLog(sprintf('(Telegram) render failed event:%s err:%s', $eventKey, $e->getMessage()));

            return;
        }

        $message = trim((string) $message);

        if ($message === '') {
            return;
        }

        $result = \Alexwaha\SmsNotify\Telegram::send($token, $chatId, $message);

        $this->writeTelegramLog(sprintf(
            '(Telegram) event:%s chat:%s ok:%d desc:%s',
            $eventKey,
            $chatId,
            !empty($result['ok']) ? 1 : 0,
            (string) ($result['description'] ?? ($result['error'] ?? ''))
        ));
    }

    private function writeTelegramLog(string $message): void
    {
        $logFilename = (string) $this->moduleConfig->get('sms_notify_log_filename', 'aw_sms_notify');
        $log = new \Log($logFilename . '.log');
        $log->write($message);
    }
}

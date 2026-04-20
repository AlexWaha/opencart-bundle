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

class ControllerExtensionModuleAwSmsNotify extends Controller
{
    private string $moduleName = 'aw_sms_notify';
    private \Alexwaha\Config $moduleConfig;
    private \Alexwaha\Language $language;
    private array $error = [];
    private string $routeExtension;
    private array $params;
    private array $tokenData;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->language = $this->awCore->getLanguage();
        $this->tokenData = $this->awCore->getToken();
        $this->moduleConfig = $this->awCore->getConfig($this->moduleName);
        $this->params = $this->language->load('extension/module/' . $this->moduleName);
        $this->params['token'] = $this->tokenData['token'];
        $this->params['token_param'] = $this->tokenData['param'];
        $this->routeExtension = $this->awCore->isLegacy()
            ? 'extension/extension'
            : 'marketplace/extension';
    }

    public function index()
    {
        $this->document->setTitle($this->language->get('heading_main_title'));
        $this->awCore->addStyles();

        $this->load->model('extension/module/' . $this->moduleName);

        $this->params['success'] = $this->session->data['success'] ?? '';
        unset($this->session->data['success']);

        $this->params['error'] = $this->error;

        $this->params['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', $this->tokenData['param'], true)
            ],
            [
                'text' => $this->language->get('text_extension'),
                'href' => $this->url->link($this->routeExtension, $this->tokenData['param'] . '&type=module', true)
            ],
            [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/module/' . $this->moduleName, $this->tokenData['param'], true)
            ]
        ];

        $this->params['action'] = $this->url->link('extension/module/' . $this->moduleName . '/store', $this->tokenData['param'], true);

        $this->params['cancel'] = $this->url->link($this->routeExtension, $this->tokenData['param'] . '&type=module', true);

        $this->params['sendMessage'] = $this->url->link('extension/module/' . $this->moduleName . '/sendMessage', $this->tokenData['param'], true);

        $this->params['clearLog'] = $this->url->link('extension/module/' . $this->moduleName . '/clearLog', $this->tokenData['param'], true);

        $this->params['telegram_detect_url'] = $this->url->link('extension/module/' . $this->moduleName . '/getTelegramChats', $this->tokenData['param'], true);

        $this->params['sms_gatenames'] = \Alexwaha\SmsNotify\SmsDispatcher::availableGateways();

        $this->params['sms_notify_gatename'] = $this->moduleConfig->get('sms_notify_gatename', '');
        $this->params['sms_notify_to'] = $this->moduleConfig->get('sms_notify_to', '');
        $this->params['sms_notify_from'] = $this->moduleConfig->get('sms_notify_from', '');
        $this->params['sms_notify_gate_username'] = $this->moduleConfig->get('sms_notify_gate_username', '');
        $this->params['sms_notify_gate_password'] = $this->moduleConfig->get('sms_notify_gate_password', '');
        $this->params['sms_notify_copy'] = $this->moduleConfig->get('sms_notify_copy', '');
        $this->params['admin_alert'] = $this->moduleConfig->get('sms_notify_admin_alert', false);
        $this->params['client_alert'] = $this->moduleConfig->get('sms_notify_client_alert', false);
        $this->params['register_alert'] = $this->moduleConfig->get('sms_notify_register_alert', false);
        $this->params['order_alert'] = $this->moduleConfig->get('sms_notify_order_alert', false);
        $this->params['reviews'] = $this->moduleConfig->get('sms_notify_reviews', false);
        $this->params['translit'] = $this->moduleConfig->get('sms_notify_translit', false);
        $this->params['force'] = $this->moduleConfig->get('sms_notify_force', false);
        $this->params['admin_template'] = $this->moduleConfig->get('sms_notify_admin_template', '');
        $this->params['client_template'] = $this->moduleConfig->get('sms_notify_client_template', []);
        $this->params['register_template'] = $this->moduleConfig->get('sms_notify_register_template', []);
        $this->params['reviews_template'] = $this->moduleConfig->get('sms_notify_reviews_template', '');
        $this->params['sms_payment'] = $this->moduleConfig->get('sms_notify_payment', []);
        $this->params['payment_template'] = $this->moduleConfig->get('sms_notify_payment_template', []);
        $this->params['sms_customer_group'] = $this->moduleConfig->get('sms_notify_customer_group', []);
        $this->params['viber_alert'] = $this->moduleConfig->get('sms_notify_viber_alert', false);
        $this->params['viber_sender'] = $this->moduleConfig->get('sms_notify_viber_sender', '');
        $this->params['viber_ttl'] = $this->moduleConfig->get('sms_notify_viber_ttl', '3600');
        $this->params['viber_caption'] = $this->moduleConfig->get('sms_notify_viber_caption', '');
        $this->params['viber_url'] = $this->moduleConfig->get('sms_notify_viber_url', '');
        $this->params['width'] = $this->moduleConfig->get('sms_notify_viber_image_width', '400');
        $this->params['height'] = $this->moduleConfig->get('sms_notify_viber_image_height', '400');
        $this->params['client_viber_template'] = $this->moduleConfig->get('sms_notify_client_viber_template', []);
        $this->params['payment_viber_template'] = $this->moduleConfig->get('sms_notify_payment_viber_template', []);
        $this->params['order_viber_template'] = $this->moduleConfig->get('sms_notify_viber_template', []);
        $this->params['sms_template'] = html_entity_decode(
            $this->moduleConfig->get('sms_notify_sms_template', ''),
            ENT_QUOTES,
            'UTF-8'
        );
        $this->params['custom_client_sms_template'] = $this->moduleConfig->get('sms_notify_custom_client_sms_template', '');
        $this->params['order_status_template'] = $this->moduleConfig->get('sms_notify_status_template', []);
        $this->params['sms_order_status'] = $this->moduleConfig->get('sms_notify_order_status', []);
        $this->params['sms_notify_log'] = $this->moduleConfig->get('sms_notify_log', true);
        $this->params['sms_notify_log_filename'] = $this->moduleConfig->get('sms_notify_log_filename', $this->moduleName);

        // Telegram channel
        $this->params['tg_enabled'] = (bool) $this->moduleConfig->get('tg_enabled', false);
        $this->params['tg_bot_token'] = (string) $this->moduleConfig->get('tg_bot_token', '');
        $this->params['tg_chat_id'] = (string) $this->moduleConfig->get('tg_chat_id', '');
        $this->params['tg_alert_order'] = (bool) $this->moduleConfig->get('tg_alert_order', false);
        $this->params['tg_alert_register'] = (bool) $this->moduleConfig->get('tg_alert_register', false);
        $this->params['tg_alert_review'] = (bool) $this->moduleConfig->get('tg_alert_review', false);
        $this->params['tg_template_order'] = $this->moduleConfig->get('tg_template_order', []);
        $this->params['tg_template_register'] = $this->moduleConfig->get('tg_template_register', []);
        $this->params['tg_template_review'] = $this->moduleConfig->get('tg_template_review', []);

        // OTP confirmation
        $this->params['otp_enabled'] = (bool) $this->moduleConfig->get('otp_enabled', false);
        $this->params['otp_protect_register'] = (bool) $this->moduleConfig->get('otp_protect_register', false);
        $this->params['otp_protect_checkout_std'] = (bool) $this->moduleConfig->get('otp_protect_checkout_std', false);
        $this->params['otp_protect_checkout_easy'] = (bool) $this->moduleConfig->get('otp_protect_checkout_easy', false);
        $this->params['otp_protect_universal'] = (bool) $this->moduleConfig->get('otp_protect_universal', false);
        $this->params['otp_code_ttl'] = (int) $this->moduleConfig->get('otp_code_ttl', 300);
        $this->params['otp_resend_throttle'] = (int) $this->moduleConfig->get('otp_resend_throttle', 30);
        $this->params['otp_max_attempts'] = (int) $this->moduleConfig->get('otp_max_attempts', 5);
        $this->params['otp_max_resends'] = (int) $this->moduleConfig->get('otp_max_resends', 2);
        $this->params['otp_lockout_duration'] = (int) $this->moduleConfig->get('otp_lockout_duration', 7200);
        $this->params['otp_template'] = $this->moduleConfig->get('otp_template', []);
        $this->params['otp_modal_title'] = $this->moduleConfig->get('otp_modal_title', []);
        $this->params['otp_modal_text'] = $this->moduleConfig->get('otp_modal_text', []);

        $this->params['payments'] = [];

        $this->params['payments'] = $this->model_extension_module_aw_sms_notify->getPaymentList();

        $this->load->model('customer/customer_group');

        $this->params['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();

        $this->load->model('tool/image');

        $this->params['viber_image'] = '';
        $this->params['viber_thumb'] = '';

        $this->params['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

        $image = $this->moduleConfig->get('sms_notify_viber_image', '');

        if ($image && is_file(DIR_IMAGE . $image)) {
            $this->params['viber_thumb'] = $this->model_tool_image->resize($image, 100, 100);
            $this->params['viber_image'] = $image;
        }

        $this->load->model('localisation/language');

        $this->params['languages'] = $this->model_localisation_language->getLanguages();

        if ($this->config->get('config_editor_default')) {
            $this->document->addScript('view/javascript/ckeditor/ckeditor.js');
            $this->document->addScript('view/javascript/ckeditor/ckeditor_init.js');
        }

        $this->params['ckeditor'] = $this->config->get('config_editor_default');

        $this->params['lang'] = $this->language->get('lang');

        $this->params['order_statuses'] = $this->model_extension_module_aw_sms_notify->getOrderStatuses();

        $logFileName = $this->params['sms_notify_log_filename'] . '.log';

        $this->params['sms_log'] = '';

        if ($this->params['sms_notify_log']) {
            $logFilePath = DIR_LOGS . $logFileName;

            if (file_exists($logFilePath)) {
                $size = filesize($logFilePath);

                if ($size >= 5242880) {
                    $suffix = [
                        'B',
                        'KB',
                        'MB',
                        'GB',
                        'TB',
                        'PB',
                        'EB',
                        'ZB',
                        'YB',
                    ];

                    $i = 0;

                    while (($size / 1024) > 1) {
                        $size = $size / 1024;
                        $i++;
                    }

                    $this->params['error_warning'] = sprintf(
                        $this->language->get('error_log_size'),
                        basename($logFilePath),
                        round(substr($size, 0, strpos($size, '.') + 4), 2) . $suffix[$i]
                    );
                } else {
                    $this->params['sms_log'] = file_get_contents($logFilePath, FILE_USE_INCLUDE_PATH, null);
                }
            }
        } else {
            $this->clearLog();
        }

        $this->params['header'] = $this->load->controller('common/header');
        $this->params['column_left'] = $this->load->controller('common/column_left');
        $this->params['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->awCore->render('extension/module/' . $this->moduleName . '/main', $this->params));
    }

    public function store()
    {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->awCore->setConfig($this->moduleName, $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link(
                'extension/module/' . $this->moduleName,
                $this->tokenData['param'] . '&type=module',
                true
            ));
        }

        $this->index();
    }

    public function order()
    {
        $this->load->model('localisation/order_status');

        $this->params['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        return $this->awCore->render('extension/module/' . $this->moduleName . '_list', $this->params);
    }

    public function orderInfoForm()
    {
        $order_id = $this->request->get['order_id'] ?? 0;

        $this->params['sendMessage'] = $this->url->link('extension/module/' . $this->moduleName . '/sendMessage', $this->tokenData['param'] . '&order_id=' . $order_id, true);

        $this->params['sms_template'] = html_entity_decode(
            $this->moduleConfig->get('sms_notify_sms_template', ''),
            ENT_QUOTES,
            'UTF-8'
        );

        $this->params['force'] = $this->moduleConfig->get('sms_notify_force', false);

        return $this->awCore->render('extension/module/' . $this->moduleName . '/order_info', $this->params);
    }

    /**
     * Event handler: inject SMS form into admin/view/sale/order_info output.
     *
     * Registered on `admin/view/sale/order_info/after`. Replaces the OCMOD patch
     * that previously inserted the form and extended the AJAX querystring.
     *
     * @param string $route
     * @param array  $data
     * @param string $output
     *
     * @return void
     */
    public function injectOrderInfoForm(&$route, &$data, &$output)
    {
        $html = $this->load->controller('extension/module/' . $this->moduleName . '/orderInfoForm');

        if (! $html) {
            return;
        }

        $marker = '<div class="tab-pane" id="tab-additional">';

        if (strpos($output, $marker) !== false) {
            $output = str_replace($marker, $html . "\n" . $marker, $output);
        }

        $ajaxNeedle = "\$('input[name=\\'notify\\']').prop('checked') ? 1 : 0)";
        $ajaxReplace = "\$('input[name=\\'notify\\']').prop('checked') ? 1 : 0) + '&sendsms=' + (\$('input[name=\\'sendsms\\']').prop('checked') ? 1 : 0) + '&admin_order=' + \$('input[name=\\'admin_order\\']').val()";
        // NOTE: added .val() relative to legacy OCMOD — original appended a jQuery object to the URL, which stringified to
        // "[object Object]" and worked only because PHP !empty() on that string still evaluated truthy. Using .val() here
        // keeps the POST value correct ("1") while preserving catalog/controller logic that only cares about truthiness.

        if (strpos($output, $ajaxNeedle) !== false) {
            $output = str_replace($ajaxNeedle, $ajaxReplace, $output);
        }
    }

    /**
     * @return void
     */
    public function sendMessage()
    {
        $json = [];

        $this->load->model('sale/order');
        $this->load->model('extension/module/' . $this->moduleName);

        if (isset($this->request->get['order_id'])) {
            $order_info = $this->model_sale_order->getOrder($this->request->get['order_id']);
        } else {
            $order_info = [];
        }

        if ((utf8_strlen($this->request->post['sms_message']) < 3)) {
            $json['error'] = $this->language->get('error_sms');
        }

        $phone = false;

        if ($this->moduleConfig->get('sms_notify_gatename') && $this->moduleConfig->get('sms_notify_gate_username')) {
            if ($order_info) {
                $phone = $this->awCore->prepareNumber($order_info['telephone']);
            } elseif ($this->request->post['phone']) {
                $phone = $this->awCore->prepareNumber($this->request->post['phone']);
            } else {
                $json['error'] = $this->language->get('error_sms');
            }
        } else {
            $json['error'] = $this->language->get('error_sms_setting');
        }

        if (! isset($json['error'])) {
            $this->load->model('tool/image');

            $viber_image_src = $this->moduleConfig->get('sms_notify_viber_image');
            $viber_image_width = $this->moduleConfig->get('sms_notify_viber_image_width');
            $viber_image_height = $this->moduleConfig->get('sms_notify_viber_image_height');

            if ($viber_image_src && is_file(DIR_IMAGE . $viber_image_src)) {
                $viber_image = $this->model_tool_image->resize(
                    $viber_image_src,
                    $viber_image_width,
                    $viber_image_height
                );
            } else {
                $viber_image = false;
            }

            $viber_options = [
                'status' => $this->moduleConfig->get('sms_notify_viber_alert'),
                'sender' => $this->moduleConfig->get('sms_notify_viber_sender'),
                'message' => $this->request->post['sms_message'],
                'ttl' => $this->moduleConfig->get('sms_notify_viber_ttl'),
                'image_url' => $viber_image ?: false,
                'caption' => $this->moduleConfig->get('sms_notify_viber_caption'),
                'action' => $this->moduleConfig->get('sms_notify_viber_url'),
            ];

            $options = [
                'to' => $phone,
                'from' => $this->moduleConfig->get('sms_notify_from'),
                'username' => $this->moduleConfig->get('sms_notify_gate_username'),
                'password' => $this->moduleConfig->get('sms_notify_gate_password'),
                'message' => $this->request->post['sms_message'],
                'viber' => $viber_options,
            ];

            $dispatcher = new SmsDispatcher(
                $this->moduleConfig->get('sms_notify_gatename'),
                $options,
                $this->moduleConfig->get('sms_notify_log_filename')
            );
            $dispatcher->send();

            if ($order_info) {
                $this->model_extension_module_aw_sms_notify->addOrderHistory(
                    $order_info['order_id'],
                    $order_info['order_status_id'],
                    $options['message']
                );
            }

            $json['success'] = $this->language->get('text_success_sms');
        }

        $this->response->setOutput(json_encode($json));
    }

    public function diagnostics(): void
    {
        $json = ['ok' => true, 'issues' => [], 'info' => [], 'sections' => []];

        if (! $this->user->hasPermission('modify', 'extension/module/' . $this->moduleName)) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'ok'       => false,
                'sections' => [],
                'error'    => $this->language->get('error_permission'),
            ]));

            return;
        }

        // Events
        $expectedEvents = [
            'aw_sms_notify_order_alert'      => 'catalog/model/checkout/order/addOrderHistory/before',
            'aw_sms_notify_review_alert'     => 'catalog/model/catalog/review/addReview/before',
            'aw_sms_notify_register_alert'   => 'catalog/model/account/customer/addCustomer/after',
            'aw_sms_notify_order_info_view'  => 'admin/view/sale/order_info/after',
        ];

        $rows = $this->db->query(
            "SELECT `code`, `trigger`, `status` FROM `" . DB_PREFIX . "event` WHERE `code` LIKE 'aw_sms_notify_%'"
        )->rows;

        $registered = [];

        foreach ($rows as $row) {
            $registered[$row['code']] = [
                'trigger' => $row['trigger'],
                'status'  => (int) ($row['status'] ?? 1),
            ];
        }

        $eventDetails = [];
        $registeredCount = 0;

        foreach ($expectedEvents as $code => $trigger) {
            $exists = isset($registered[$code]);
            $enabled = $exists && $registered[$code]['status'] === 1 && $registered[$code]['trigger'] === $trigger;

            if ($exists && $enabled) {
                $registeredCount++;
            }

            $eventDetails[] = [
                'code'    => $code,
                'trigger' => $trigger,
                'exists'  => $exists,
                'enabled' => $enabled,
            ];
        }

        $json['sections']['events'] = [
            'ok'         => $registeredCount === count($expectedEvents),
            'total'      => count($expectedEvents),
            'registered' => $registeredCount,
            'details'    => $eventDetails,
        ];

        // Required config
        $missing = [];

        if (empty($this->moduleConfig->get('sms_notify_to', ''))) {
            $missing[] = ['field' => 'sms_notify_to', 'tab' => 'gate'];
        }

        if (empty($this->moduleConfig->get('sms_notify_gatename', ''))) {
            $missing[] = ['field' => 'sms_notify_gatename', 'tab' => 'gate'];
        }

        if (empty($this->moduleConfig->get('sms_notify_gate_username', ''))) {
            $missing[] = ['field' => 'sms_notify_gate_username', 'tab' => 'gate'];
        }

        if (empty($this->moduleConfig->get('sms_notify_from', ''))) {
            $missing[] = ['field' => 'sms_notify_from', 'tab' => 'gate'];
        }

        if ((int) $this->moduleConfig->get('sms_notify_admin_alert', 0) === 1
            && empty(trim((string) $this->moduleConfig->get('sms_notify_admin_template', '')))) {
            $missing[] = ['field' => 'sms_notify_admin_template', 'tab' => 'template'];
        }

        if ((int) $this->moduleConfig->get('sms_notify_reviews', 0) === 1
            && empty(trim((string) $this->moduleConfig->get('sms_notify_reviews_template', '')))) {
            $missing[] = ['field' => 'sms_notify_reviews_template', 'tab' => 'template'];
        }

        $json['sections']['config'] = [
            'ok'      => empty($missing),
            'missing' => $missing,
        ];

        // Telegram
        $tgEnabled = (bool) $this->moduleConfig->get('tg_enabled', false);
        $tgToken = trim((string) $this->moduleConfig->get('tg_bot_token', ''));
        $tgChatId = trim((string) $this->moduleConfig->get('tg_chat_id', ''));

        $tgChecks = [];

        if (!$tgEnabled) {
            $tgChecks[] = [
                'key'    => 'enabled',
                'ok'     => true,
                'status' => $this->language->get('text_diag_tg_disabled'),
            ];

            $json['sections']['telegram'] = [
                'ok'      => true,
                'enabled' => false,
                'details' => $tgChecks,
            ];
        } else {
            // Bot reachable
            if ($tgToken === '') {
                $tgChecks[] = [
                    'key'    => 'bot_reachable',
                    'ok'     => false,
                    'status' => $this->language->get('text_diag_tg_fail'),
                    'details' => $this->language->get('error_tg_token'),
                ];
            } else {
                $response = \Alexwaha\SmsNotify\Telegram::getMe($tgToken);
                $reachable = !empty($response['ok']);
                $username = $response['result']['username'] ?? '';

                $tgChecks[] = [
                    'key'    => 'bot_reachable',
                    'ok'     => $reachable,
                    'status' => $reachable
                        ? $this->language->get('text_diag_tg_ok')
                        : $this->language->get('text_diag_tg_fail'),
                    'details' => $reachable
                        ? '@' . $username
                        : (string) ($response['description'] ?? ($response['error'] ?? '')),
                ];
            }

            // Chat configured
            $tgChecks[] = [
                'key'    => 'chat_configured',
                'ok'     => $tgChatId !== '',
                'status' => $tgChatId !== ''
                    ? $this->language->get('text_diag_tg_chat_ok')
                    : $this->language->get('text_diag_tg_chat_missing'),
                'details' => $tgChatId,
            ];

            // Templates present for enabled events (default language only)
            $defaultLanguageId = (int) $this->config->get('config_language_id');
            $alertMap = [
                'order'    => 'tg_template_order',
                'register' => 'tg_template_register',
                'review'   => 'tg_template_review',
            ];

            $templatesOk = true;
            $templatesAtLeastOne = false;

            foreach ($alertMap as $eventKey => $tplKey) {
                if (!(bool) $this->moduleConfig->get('tg_alert_' . $eventKey, false)) {
                    continue;
                }

                $templatesAtLeastOne = true;
                $tplArr = (array) $this->moduleConfig->get($tplKey, []);
                $tplValue = trim((string) ($tplArr[$defaultLanguageId] ?? ''));

                if ($tplValue === '') {
                    $templatesOk = false;
                    break;
                }
            }

            $tgChecks[] = [
                'key'    => 'templates_present',
                'ok'     => !$templatesAtLeastOne || $templatesOk,
                'status' => (!$templatesAtLeastOne || $templatesOk)
                    ? $this->language->get('text_diag_tg_templates_ok')
                    : $this->language->get('text_diag_tg_templates_missing'),
            ];

            $sectionOk = true;

            foreach ($tgChecks as $check) {
                if (empty($check['ok'])) {
                    $sectionOk = false;
                    break;
                }
            }

            $json['sections']['telegram'] = [
                'ok'      => $sectionOk,
                'enabled' => true,
                'details' => $tgChecks,
            ];
        }

        // OTP
        $otpEnabled = (bool) $this->moduleConfig->get('otp_enabled', false);

        if (!$otpEnabled) {
            $json['sections']['otp'] = [
                'ok'      => true,
                'enabled' => false,
                'details' => [
                    [
                        'key'    => 'disabled',
                        'ok'     => true,
                        'status' => $this->language->get('text_diag_otp_disabled'),
                    ],
                ],
            ];
        } else {
            $otpChecks = [];

            // OTP events registered
            $otpExpectedEvents = [
                'aw_sms_notify_otp_register'       => 'catalog/controller/account/register/before',
                'aw_sms_notify_otp_checkout_std'   => 'catalog/controller/checkout/guest/save/before',
                'aw_sms_notify_otp_checkout_easy'  => 'catalog/controller/extension/aw_easy_checkout/validation/before',
                'aw_sms_notify_otp_addorder'       => 'catalog/model/checkout/order/addOrder/before',
                'aw_sms_notify_otp_addcustomer'    => 'catalog/model/account/customer/addCustomer/before',
                'aw_sms_notify_otp_assets'         => 'catalog/view/*/footer/after',
            ];

            $otpEventDetails = [];
            $otpRegisteredCount = 0;

            foreach ($otpExpectedEvents as $code => $trigger) {
                $exists = isset($registered[$code]);
                $enabled = $exists && $registered[$code]['status'] === 1 && $registered[$code]['trigger'] === $trigger;

                if ($enabled) {
                    $otpRegisteredCount++;
                }

                $otpEventDetails[] = [
                    'code'    => $code,
                    'trigger' => $trigger,
                    'exists'  => $exists,
                    'enabled' => $enabled,
                ];
            }

            $otpEventsAllOk = $otpRegisteredCount === count($otpExpectedEvents);

            $otpChecks[] = [
                'key'     => 'events',
                'ok'      => $otpEventsAllOk,
                'status'  => $otpEventsAllOk
                    ? $this->language->get('text_diag_otp_events_ok')
                    : $this->language->get('text_diag_otp_events_missing'),
                'events'  => $otpEventDetails,
            ];

            // SMS gateway available
            $gateName = trim((string) $this->moduleConfig->get('sms_notify_gatename', ''));
            $gateUser = trim((string) $this->moduleConfig->get('sms_notify_gate_username', ''));
            $gatewayOk = $gateName !== '' && $gateUser !== '';

            $otpChecks[] = [
                'key'    => 'sms_gateway',
                'ok'     => $gatewayOk,
                'status' => $gatewayOk
                    ? $this->language->get('text_diag_otp_gateway_ok')
                    : $this->language->get('text_diag_otp_no_gateway'),
            ];

            // Templates completeness for enabled scopes
            $defaultLanguageId = (int) $this->config->get('config_language_id');
            $scopeToggles = [
                'otp_protect_register',
                'otp_protect_checkout_std',
                'otp_protect_checkout_easy',
                'otp_protect_universal',
            ];

            $anyScopeActive = false;

            foreach ($scopeToggles as $toggle) {
                if ((bool) $this->moduleConfig->get($toggle, false)) {
                    $anyScopeActive = true;
                    break;
                }
            }

            $otpTemplate = (array) $this->moduleConfig->get('otp_template', []);
            $otpTemplateValue = trim((string) ($otpTemplate[$defaultLanguageId] ?? ''));
            $templatesOk = !$anyScopeActive || $otpTemplateValue !== '';

            $otpChecks[] = [
                'key'    => 'templates',
                'ok'     => $templatesOk,
                'status' => $templatesOk
                    ? $this->language->get('text_diag_otp_templates_ok')
                    : $this->language->get('text_diag_otp_templates_missing'),
            ];

            // Modal i18n — ok=true with info if falling back to defaults
            $otpModalTitle = (array) $this->moduleConfig->get('otp_modal_title', []);
            $otpModalText = (array) $this->moduleConfig->get('otp_modal_text', []);
            $titleValue = trim((string) ($otpModalTitle[$defaultLanguageId] ?? ''));
            $textValue = trim((string) ($otpModalText[$defaultLanguageId] ?? ''));
            $modalCustomized = $titleValue !== '' && $textValue !== '';

            $otpChecks[] = [
                'key'     => 'modal_i18n',
                'ok'      => true,
                'status'  => $modalCustomized
                    ? $this->language->get('text_diag_otp_modal_custom')
                    : $this->language->get('text_diag_otp_modal_defaults'),
            ];

            $sectionOk = true;

            foreach ($otpChecks as $check) {
                if (empty($check['ok'])) {
                    $sectionOk = false;
                    break;
                }
            }

            $json['sections']['otp'] = [
                'ok'      => $sectionOk,
                'enabled' => true,
                'details' => $otpChecks,
            ];
        }

        // Log file
        $logFilename = (string) $this->moduleConfig->get('sms_notify_log_filename', $this->moduleName);
        $logEnabled = (bool) $this->moduleConfig->get('sms_notify_log', true);
        $logPath = DIR_LOGS . $logFilename . '.log';
        $logExists = is_file($logPath);
        $logSize = $logExists ? (int) @filesize($logPath) : 0;

        $json['sections']['log'] = [
            'enabled'    => $logEnabled,
            'filename'   => $logFilename . '.log',
            'exists'     => $logExists,
            'size'       => $logSize,
            'size_human' => $this->humanFileSize($logSize),
        ];

        // Top-level ok flag
        $json['ok'] = $json['sections']['events']['ok']
            && $json['sections']['config']['ok']
            && (!isset($json['sections']['telegram']) || $json['sections']['telegram']['ok'])
            && (!isset($json['sections']['otp']) || $json['sections']['otp']['ok']);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function humanFileSize(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = (int) floor(log($bytes, 1024));
        $i = min($i, count($units) - 1);

        return round($bytes / (1024 ** $i), 2) . ' ' . $units[$i];
    }

    public function clearLog()
    {
        $json = [];

        $logFileName = $this->moduleConfig->get('sms_notify_log_filename');

        $logFile = DIR_LOGS . $logFileName . '.log';

        if (! file_exists($logFile)) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (! $this->user->hasPermission('modify', 'extension/module/' . $this->moduleName)) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            file_put_contents($logFile, '');
            $json['success'] = $this->language->get('text_success_log');
        }

        $this->response->setOutput(json_encode($json));
    }

    public function getTelegramChats(): void
    {
        $this->response->addHeader('Content-Type: application/json');

        $json = ['chats' => []];

        if (! $this->user->hasPermission('modify', 'extension/module/' . $this->moduleName)) {
            $json['error'] = $this->language->get('error_permission');
            $this->response->setOutput(json_encode($json));

            return;
        }

        $this->load->language('extension/module/' . $this->moduleName);

        $token = trim((string) ($this->request->post['tg_bot_token'] ?? ''));

        if ($token === '') {
            $json['error'] = $this->language->get('error_tg_token');
            $this->response->setOutput(json_encode($json));

            return;
        }

        $response = \Alexwaha\SmsNotify\Telegram::getUpdates($token);

        if (empty($response['ok'])) {
            $json['error'] = (string) (
                $response['description']
                ?? $response['error']
                ?? $this->language->get('error_tg_detect_failed')
            );
            $this->response->setOutput(json_encode($json));

            return;
        }

        $chats = \Alexwaha\SmsNotify\Telegram::extractChats($response);

        if (empty($chats)) {
            $json['empty'] = true;
            $json['message'] = $this->language->get('text_tg_no_chats_found');
        } else {
            $json['chats'] = $chats;
        }

        $this->response->setOutput(json_encode($json));
    }

    protected function validate(): bool
    {
        if (! $this->user->hasPermission('modify', 'extension/module/' . $this->moduleName)) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (isset($this->request->post['sms_notify_log_filename']) && !$this->request->post['sms_notify_log_filename']) {
            $this->error['log_filename'] = $this->language->get('error_log_filename');
        }

        if (isset($this->request->post['sms_notify_gatename']) && !$this->request->post['sms_notify_gatename']) {
            $this->error['gatename'] = $this->language->get('error_gatename');
        }

        if (isset($this->request->post['sms_notify_from']) && !$this->request->post['sms_notify_from']) {
            $this->error['from'] = $this->language->get('error_from');
        }

        if (isset($this->request->post['sms_notify_gate_username']) && !$this->request->post['sms_notify_gate_username']) {
            $this->error['username'] = $this->language->get('error_username');
        }

        if ((isset($this->request->post['sms_notify_admin_alert']) && $this->request->post['sms_notify_admin_alert'])
            && isset($this->request->post['sms_notify_admin_template'])) {
            if ((utf8_strlen(trim($this->request->post['sms_notify_admin_template'])) < 3)) {
                $this->error['admin_template'] = $this->language->get('error_admin_template');
            }
        }

        if ((isset($this->request->post['sms_notify_reviews']) && $this->request->post['sms_notify_reviews'])
            && isset($this->request->post['sms_notify_reviews_template'])) {
            if ((utf8_strlen(trim($this->request->post['sms_notify_reviews_template'])) < 3)) {
                $this->error['reviews_template'] = $this->language->get('error_reviews_template');
            }
        }

        if ((isset($this->request->post['sms_notify_client_alert']) && $this->request->post['sms_notify_client_alert'])) {
            if (isset($this->request->post['sms_notify_client_template'])) {
                foreach ($this->request->post['sms_notify_client_template'] as $languageId => $value) {
                    if ((utf8_strlen(trim($value)) < 3)) {
                        $this->error['client_template'][$languageId] = $this->language->get('error_client_template');
                    }
                }
            }
        }

        if ((isset($this->request->post['sms_notify_register_alert']) && $this->request->post['sms_notify_register_alert'])) {
            if (isset($this->request->post['sms_notify_register_template'])) {
                foreach ($this->request->post['sms_notify_register_template'] as $languageId => $value) {
                    if ((utf8_strlen(trim($value)) < 3)) {
                        $this->error['register_template'][$languageId] = $this->language->get('error_register_template');
                    }
                }
            }
        }

        if ((isset($this->request->post['sms_notify_viber_alert']) && $this->request->post['sms_notify_viber_alert'])
            && isset($this->request->post['sms_notify_viber_sender'])) {
            if ((utf8_strlen(trim($this->request->post['sms_notify_viber_sender'])) < 3)) {
                $this->error['viber_sender'] = $this->language->get('error_viber_sender');
            }
        }

        if ((isset($this->request->post['sms_notify_viber_alert']) && $this->request->post['sms_notify_viber_alert'])) {
            if (isset($this->request->post['sms_notify_client_viber_template'])) {
                foreach ($this->request->post['sms_notify_client_viber_template'] as $languageId => $value) {
                    if ((utf8_strlen(trim($value)) < 3)) {
                        $this->error['client_viber_template'][$languageId] = $this->language->get('error_client_viber_template');
                    }
                }
            }
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }

    /**
     * @return void
     */
    public function install()
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('module_' . $this->moduleName, ['module_' . $this->moduleName . '_status' => '1']);

        $this->awCore->removeLegacyFiles($this->getLegacyList());
        $this->uninstallLegacyEvents();
        $this->installEvents();
        $this->installPermissions();
    }

    /**
     * @return void
     */
    protected function installEvents()
    {
        if ($this->awCore->isLegacy()) {
            $this->load->model('extension/event');
            $model = 'model_extension_event';
        } else {
            $this->load->model('setting/event');
            $model = 'model_setting_event';
        }

        $this->{$model}->addEvent(
            'aw_sms_notify_order_alert',
            'catalog/model/checkout/order/addOrderHistory/before',
            'extension/module/aw_sms_notify/order',
            1
        );

        $this->{$model}->addEvent(
            'aw_sms_notify_review_alert',
            'catalog/model/catalog/review/addReview/before',
            'extension/module/aw_sms_notify/review',
            1
        );

        $this->{$model}->addEvent(
            'aw_sms_notify_register_alert',
            'catalog/model/account/customer/addCustomer/after',
            'extension/module/aw_sms_notify/register',
            1
        );

        $this->{$model}->addEvent(
            'aw_sms_notify_order_info_view',
            'admin/view/sale/order_info/after',
            'extension/module/aw_sms_notify/injectOrderInfoForm',
            1
        );

        // OTP gate events (defense in depth)
        $this->{$model}->addEvent(
            'aw_sms_notify_otp_register',
            'catalog/controller/account/register/before',
            'extension/module/aw_sms_notify/enforceOtpRegister',
            1
        );

        $this->{$model}->addEvent(
            'aw_sms_notify_otp_checkout_std',
            'catalog/controller/checkout/guest/save/before',
            'extension/module/aw_sms_notify/enforceOtpCheckoutStd',
            1
        );

        $this->{$model}->addEvent(
            'aw_sms_notify_otp_checkout_easy',
            'catalog/controller/extension/aw_easy_checkout/validation/before',
            'extension/module/aw_sms_notify/enforceOtpCheckoutEasy',
            1
        );

        $this->{$model}->addEvent(
            'aw_sms_notify_otp_addorder',
            'catalog/model/checkout/order/addOrder/before',
            'extension/module/aw_sms_notify/enforceOtpAddOrder',
            1
        );

        $this->{$model}->addEvent(
            'aw_sms_notify_otp_addcustomer',
            'catalog/model/account/customer/addCustomer/before',
            'extension/module/aw_sms_notify/enforceOtpAddCustomer',
            1
        );

        $this->{$model}->addEvent(
            'aw_sms_notify_otp_assets',
            'catalog/view/*/footer/after',
            'extension/module/aw_sms_notify/injectOtpAssets',
            1
        );
    }

    /**
     * @return void
     */
    protected function installPermissions()
    {
        $this->load->model('user/user_group');

        $this->model_user_user_group->addPermission(
            $this->user->getGroupId(),
            'access',
            'extension/module/' . $this->moduleName
        );
        $this->model_user_user_group->addPermission(
            $this->user->getGroupId(),
            'modify',
            'extension/module/' . $this->moduleName
        );
    }

    /**
     * @return void
     */
    public function uninstall()
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('sms_notify');
        $this->awCore->removeConfig($this->moduleName);

        if ($this->awCore->isLegacy()) {
            $this->load->model('extension/event');

            $this->model_extension_event->deleteEvent('aw_sms_notify_order_alert');
            $this->model_extension_event->deleteEvent('aw_sms_notify_review_alert');
            $this->model_extension_event->deleteEvent('aw_sms_notify_register_alert');
            $this->model_extension_event->deleteEvent('aw_sms_notify_order_info_view');
            $this->model_extension_event->deleteEvent('aw_sms_notify_otp_register');
            $this->model_extension_event->deleteEvent('aw_sms_notify_otp_checkout_std');
            $this->model_extension_event->deleteEvent('aw_sms_notify_otp_checkout_easy');
            $this->model_extension_event->deleteEvent('aw_sms_notify_otp_addorder');
            $this->model_extension_event->deleteEvent('aw_sms_notify_otp_addcustomer');
            $this->model_extension_event->deleteEvent('aw_sms_notify_otp_assets');
        } else {
            $this->load->model('setting/event');

            $this->model_setting_event->deleteEventByCode('aw_sms_notify_order_alert');
            $this->model_setting_event->deleteEventByCode('aw_sms_notify_review_alert');
            $this->model_setting_event->deleteEventByCode('aw_sms_notify_register_alert');
            $this->model_setting_event->deleteEventByCode('aw_sms_notify_order_info_view');
            $this->model_setting_event->deleteEventByCode('aw_sms_notify_otp_register');
            $this->model_setting_event->deleteEventByCode('aw_sms_notify_otp_checkout_std');
            $this->model_setting_event->deleteEventByCode('aw_sms_notify_otp_checkout_easy');
            $this->model_setting_event->deleteEventByCode('aw_sms_notify_otp_addorder');
            $this->model_setting_event->deleteEventByCode('aw_sms_notify_otp_addcustomer');
            $this->model_setting_event->deleteEventByCode('aw_sms_notify_otp_assets');
        }
    }

    /**
     * @return void
     */
    public function uninstallLegacyEvents()
    {
        if ($this->awCore->isLegacy()) {
            $this->load->model('extension/event');

            $this->model_extension_event->deleteEvent('ocd_sms_notify_order_alert');
            $this->model_extension_event->deleteEvent('ocd_sms_notify_review_alert');
            $this->model_extension_event->deleteEvent('ocd_sms_notify_register_alert');
            $this->model_extension_event->deleteEvent('ocd_sms_notify_orderproh');
            $this->model_extension_event->deleteEvent('ochelp_sms_notify_order_alert');
            $this->model_extension_event->deleteEvent('ochelp_sms_notify_review_alert');
        } else {
            $this->load->model('setting/event');

            $this->model_setting_event->deleteEventByCode('ocd_sms_notify_order_alert');
            $this->model_setting_event->deleteEventByCode('ocd_sms_notify_review_alert');
            $this->model_setting_event->deleteEventByCode('ocd_sms_notify_register_alert');
            $this->model_setting_event->deleteEventByCode('ocd_sms_notify_orderproh');
            $this->model_setting_event->deleteEventByCode('ochelp_sms_notify_order_alert');
            $this->model_setting_event->deleteEventByCode('ochelp_sms_notify_review_alert');
        }
    }

    public function exportConfig()
    {
        if (! $this->validate()) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'error' => $this->language->get('error_permission'),
            ]));

            return;
        }

        try {
            $jsonData = $this->awCore->exportConfig($this->moduleName);

            $filename = $this->moduleName . '_settings_' . date('Y-m-d_H-i-s') . '.json';

            $this->response->addHeader('Content-Type: application/json');
            $this->response->addHeader('Content-Disposition: attachment; filename="' . $filename . '"');
            $this->response->setOutput($jsonData);

        } catch (Exception $e) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'error' => sprintf($this->language->get('error_export_failed'), $e->getMessage()),
            ]));
        }
    }

    public function importConfig()
    {
        $json = [];

        if (! $this->validate()) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            if (isset($this->request->files['import_file']) && is_uploaded_file($this->request->files['import_file']['tmp_name'])) {
                try {
                    $fileContent = file_get_contents($this->request->files['import_file']['tmp_name']);

                    if ($fileContent === false) {
                        throw new Exception($this->language->get('error_import_read_file'));
                    }

                    $this->awCore->importConfig($this->moduleName, $fileContent);

                    $json['success'] = $this->language->get('text_import_success');

                } catch (Exception $e) {
                    $json['error'] = sprintf($this->language->get('error_import_failed'), $e->getMessage());
                }
            } else {
                $json['error'] = $this->language->get('error_import_file');
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    protected function getLegacyList(): array
    {
        return [
            DIR_APPLICATION . 'controller/extension/module/ocd_sms_notify.php',
            DIR_APPLICATION . 'controller/extension/module/aw_sms_event.php',
            DIR_APPLICATION . 'controller/module/ocd_sms_notify.php',
            DIR_APPLICATION . 'model/extension/module/ocd_sms_notify.php',
            DIR_APPLICATION . 'model/extension/module/aw_sms_event.php',
            DIR_APPLICATION . 'model/module/ocd_sms_notify.php',
            DIR_APPLICATION . 'view/template/extension/module/ocd_sms_notify.tpl',
            DIR_APPLICATION . 'view/template/extension/module/ocd_sms_notify_info.tpl',
            DIR_APPLICATION . 'view/template/extension/module/ocd_sms_notify_list.tpl',
            DIR_APPLICATION . 'view/template/extension/module/ocd_sms_notify.twig',
            DIR_APPLICATION . 'view/template/extension/module/ocd_sms_notify_info.twig',
            DIR_APPLICATION . 'view/template/extension/module/ocd_sms_notify_list.twig',
            DIR_APPLICATION . 'view/template/module/ocd_sms_notify.tpl',
            DIR_APPLICATION . 'view/template/module/ocd_sms_notify_info.tpl',
            DIR_APPLICATION . 'view/template/module/ocd_sms_notify_list.tpl',
            DIR_APPLICATION . 'language/en-gb/extension/module/ocd_sms_notify.php',
            DIR_APPLICATION . 'language/english/module/ocd_sms_notify.php',
            DIR_APPLICATION . 'language/ru-ru/extension/module/ocd_sms_notify.php',
            DIR_APPLICATION . 'language/russian/module/ocd_sms_notify.php',
            DIR_APPLICATION . 'language/en-gb/module/ocd_sms_notify.php',
            DIR_APPLICATION . 'language/ru-ru/module/ocd_sms_notify.php',
            DIR_CATALOG . 'controller/extension/module/ocd_sms_notify.php',
            DIR_CATALOG . 'controller/module/ocd_sms_notify.php',
            DIR_CATALOG . 'model/extension/module/ocd_sms_notify.php',
            DIR_CATALOG . 'model/module/ocd_sms_notify.php',
            DIR_APPLICATION . 'controller/extension/module/ochelp_sms_notify.php',
            DIR_APPLICATION . 'controller/module/ochelp_sms_notify.php',
            DIR_APPLICATION . 'model/extension/module/ochelp_sms_notify.php',
            DIR_APPLICATION . 'model/module/ochelp_sms_notify.php',
            DIR_APPLICATION . 'view/template/extension/module/ochelp_sms_notify.tpl',
            DIR_APPLICATION . 'view/template/extension/module/ochelp_sms_notify_info.tpl',
            DIR_APPLICATION . 'view/template/extension/module/ochelp_sms_notify_list.tpl',
            DIR_APPLICATION . 'view/template/extension/module/ochelp_sms_notify.twig',
            DIR_APPLICATION . 'view/template/extension/module/ochelp_sms_notify_info.twig',
            DIR_APPLICATION . 'view/template/extension/module/ochelp_sms_notify_list.twig',
            DIR_APPLICATION . 'view/template/module/ochelp_sms_notify.tpl',
            DIR_APPLICATION . 'view/template/module/ochelp_sms_notify_info.tpl',
            DIR_APPLICATION . 'view/template/module/ochelp_sms_notify_list.tpl',
            DIR_APPLICATION . 'language/en-gb/extension/module/ochelp_sms_notify.php',
            DIR_APPLICATION . 'language/english/module/ochelp_sms_notify.php',
            DIR_APPLICATION . 'language/ru-ru/extension/module/ochelp_sms_notify.php',
            DIR_APPLICATION . 'language/russian/module/ochelp_sms_notify.php',
            DIR_APPLICATION . 'language/en-gb/module/ochelp_sms_notify.php',
            DIR_APPLICATION . 'language/ru-ru/module/ochelp_sms_notify.php',
            DIR_CATALOG . 'controller/extension/module/ochelp_sms_notify.php',
            DIR_CATALOG . 'controller/module/ochelp_sms_notify.php',
            DIR_CATALOG . 'model/extension/module/ochelp_sms_notify.php',
            DIR_CATALOG . 'model/module/ochelp_sms_notify.php',
            DIR_SYSTEM . 'smsgate',
            DIR_SYSTEM . 'library/smsgate',
            DIR_SYSTEM . 'library/ocdsms.php',
            DIR_LOGS . 'sms_log.log',
        ];
    }
}

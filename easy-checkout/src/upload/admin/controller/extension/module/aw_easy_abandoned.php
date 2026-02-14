<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionModuleAwEasyAbandoned extends Controller
{
    private string $moduleParentName = 'aw_easy_checkout';

    private string $moduleName = 'aw_easy_abandoned';

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
        $this->routeExtension = $this->awCore->isLegacy() ? 'extension/extension' : 'marketplace/extension';
    }

    public function index()
    {
        $this->document->setTitle($this->language->get('heading_main_title'));
        $this->awCore->addStyles();

        $this->load->model('extension/module/' . $this->moduleName);

        $this->getList();
    }

    protected function getList()
    {
        $this->document->setTitle($this->language->get('heading_main_title'));

        $this->params['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', $this->tokenData['param'], true),
            ],
            [
                'text' => $this->language->get('text_extension'),
                'href' => $this->url->link($this->routeExtension, $this->tokenData['param'] . '&type=module', true),
            ],
            [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/module/' . $this->moduleName, $this->tokenData['param'], true),
            ],
        ];

        $this->params['success'] = $this->session->data['success'] ?? '';
        unset($this->session->data['success']);

        $this->params['error'] = $this->error;

        $filterAbandonedId = $this->request->get['filter_abandoned_id'] ?? null;
        $filterCustomer = $this->request->get['filter_customer'] ?? null;
        $filterCreatedAt = $this->request->get['filter_created_at'] ?? null;
        $sort = $this->request->get['sort'] ?? 'a.created_at';
        $order = $this->request->get['order'] ?? 'DESC';
        $page = $this->request->get['page'] ?? 1;
        $limit = $this->config->get('config_limit_admin');

        $this->params['status_email'] = $this->moduleConfig->get('status_email');
        $this->params['status_sms'] = $this->moduleConfig->get('status_sms');
        $this->params['sms_module_installed'] = file_exists(DIR_APPLICATION . 'controller/extension/module/aw_sms_notify.php');

        $url = '';

        if (isset($this->request->get['filter_abandoned_id'])) {
            $url .= '&filter_abandoned_id=' . $this->request->get['filter_abandoned_id'];
        }

        if (isset($this->request->get['filter_customer'])) {
            $url .= '&filter_customer=' . urlencode(html_entity_decode(
                $this->request->get['filter_customer'],
                ENT_QUOTES,
                'UTF-8'
            ));
        }

        if (isset($this->request->get['filter_created_at'])) {
            $url .= '&filter_created_at=' . $this->request->get['filter_created_at'];
        }

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $this->params['delete'] = $this->url->link(
            'extension/module/' . $this->moduleName . '/delete',
            $this->tokenData['param'],
            true
        );
        $this->params['setting'] = $this->url->link(
            'extension/module/' . $this->moduleName . '/setting',
            $this->tokenData['param'],
            true
        );

        $this->params['orders'] = [];

        $filterData = [
            'filter_abandoned_id' => $filterAbandonedId,
            'filter_customer' => $filterCustomer,
            'filter_created_at' => $filterCreatedAt,
            'sort' => $sort,
            'order' => $order,
            'start' => $limit * ($page - 1),
            'limit' => $limit,
        ];

        $total = $this->model_extension_module_aw_easy_abandoned->getTotalOrders($filterData);
        $results = $this->model_extension_module_aw_easy_abandoned->getOrders($filterData);

        if (! empty($results)) {
            foreach ($results as $result) {
                $this->params['orders'][] = [
                    'abandoned_id' => $result['abandoned_id'],
                    'customer' => $result['customer'],
                    'telephone' => $result['telephone'],
                    'email' => $result['email'],
                    'email_sent_at' => ! empty($result['email_sent_at']) ? $this->language->get('text_send_message') . $result['email_sent_at'] : '',
                    'sms_sent_at' => ! empty($result['sms_sent_at']) ? $this->language->get('text_send_sms_message') . $result['sms_sent_at'] : '',
                    'viewed' => ! empty($result['viewed']),
                    'created_at' => date($this->language->get('datetime_format'), strtotime($result['created_at'])),
                    'delete' => $this->url->link(
                        'extension/module/' . $this->moduleName . '/delete',
                        $this->tokenData['param'] . '&abandoned_id=' . $result['abandoned_id'] . $url,
                        true
                    ),
                ];
            }
        }

        $this->params['selected'] = $this->request->post['selected'] ?? [];

        $url = '';

        if (isset($this->request->get['filter_abandoned_id'])) {
            $url .= '&filter_abandoned_id=' . $this->request->get['filter_abandoned_id'];
        }

        if (isset($this->request->get['filter_customer'])) {
            $url .= '&filter_customer=' . urlencode(html_entity_decode(
                $this->request->get['filter_customer'],
                ENT_QUOTES,
                'UTF-8'
            ));
        }

        if (isset($this->request->get['filter_created_at'])) {
            $url .= '&filter_created_at=' . $this->request->get['filter_created_at'];
        }

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $this->params['sort_order'] = $this->url->link(
            'extension/module/' . $this->moduleName,
            $this->tokenData['param'] . '&sort=a.abandoned_id' . $url,
            true
        );
        $this->params['sort_created_at'] = $this->url->link(
            'extension/module/' . $this->moduleName,
            $this->tokenData['param'] . '&sort=a.created_at' . $url,
            true
        );

        $url = '';

        if (isset($this->request->get['filter_abandoned_id'])) {
            $url .= '&filter_abandoned_id=' . $this->request->get['filter_abandoned_id'];
        }

        if (isset($this->request->get['filter_customer'])) {
            $url .= '&filter_customer=' . urlencode(html_entity_decode(
                $this->request->get['filter_customer'],
                ENT_QUOTES,
                'UTF-8'
            ));
        }

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link(
            'extension/module/' . $this->moduleName,
            $this->tokenData['param'] . $url . '&page={page}',
            true
        );

        $this->params['pagination'] = $pagination->render();

        $this->params['results'] = sprintf(
            $this->language->get('text_pagination'),
            ($total) ? (($page - 1) * $limit) + 1 : 0,
            (((($page - 1) * $limit) > ($total - $limit)) ? $total : ((($page - 1) * $limit) + $limit)),
            $total,
            ceil($total / $limit)
        );

        $this->params['filter_abandoned_id'] = $filterAbandonedId;
        $this->params['filter_customer'] = $filterCustomer;
        $this->params['filter_created_at'] = $filterCreatedAt;

        $this->params['sort'] = $sort;
        $this->params['order'] = $order;

        $this->params['header'] = $this->load->controller('common/header');
        $this->params['column_left'] = $this->load->controller('common/column_left');
        $this->params['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->awCore->render('extension/module/' . $this->moduleName . '/main', $this->params));
    }

    private function getCustomFields($orderInfo, $variables)
    {
        $variables = str_replace('[firstname]', $orderInfo['firstname'], $variables);
        $variables = str_replace('[lastname]', $orderInfo['lastname'], $variables);
        $variables = str_replace('[email]', $orderInfo['email'], $variables);
        $variables = str_replace('[telephone]', $orderInfo['telephone'], $variables);
        $variables = str_replace('[created_at]', $orderInfo['created_at'], $variables);

        $products = '';

        foreach ($orderInfo['products'] as $product) {
            $product_string = '<a href="' . $product['href'] . '">' . $product['name'] . '</a> (' . $product['model'] . ') - ' . $product['quantity'] . $this->language->get('text_qty') . ' x ' . $product['price'] . ' = ' . $product['total'];
            $products .= $product_string . '<br><br>';
        }

        return str_replace('[products]', $products, $variables);
    }

    public function setting()
    {
        $this->document->setTitle($this->language->get('heading_title_setting'));

        $this->awCore->addStyles();

        $this->document->addStyle('view/javascript/summernote/summernote.css');
        $this->document->addScript('view/javascript/summernote/summernote.js');

        if (! $this->awCore->isLegacy()) {
            $this->document->addScript('view/javascript/summernote/summernote-image-attributes.js');
        }

        $this->document->addScript('view/javascript/summernote/opencart.js');

        $this->load->model('extension/module/' . $this->moduleName);

        $this->params['success'] = $this->session->data['success'] ?? '';
        unset($this->session->data['success']);

        $this->params['error'] = $this->error;

        $this->params['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', $this->tokenData['param'], true),
            ],
            [
                'text' => $this->language->get('text_extension'),
                'href' => $this->url->link($this->routeExtension, $this->tokenData['param'] . '&type=module', true),
            ],
            [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/module/' . $this->moduleName, $this->tokenData['param'], true),
            ],
            [
                'text' => $this->language->get('heading_title_setting'),
                'href' => $this->url->link(
                    'extension/module/' . $this->moduleName . '/setting',
                    $this->tokenData['param'],
                    true
                ),
            ],
        ];

        $this->params['show_sms'] = false;

        if ($this->user->hasPermission('modify', 'extension/module/aw_sms_notify')) {
            $this->params['show_sms'] = true;
        }

        $this->params['status'] = $this->moduleConfig->get('status', 0);
        $this->params['status_email'] = $this->moduleConfig->get('status_email', 0);
        $this->params['email_subject'] = $this->moduleConfig->get('email_subject', []);
        $this->params['email_template'] = $this->moduleConfig->get('email_template', []);

        $this->params['status_sms'] = $this->moduleConfig->get('status_sms', 0);
        $this->params['sms_template'] = $this->moduleConfig->get('sms_template', []);

        $this->load->model('localisation/language');
        $this->params['languages'] = $this->model_localisation_language->getLanguages();

        $this->params['action'] = $this->url->link(
            'extension/module/' . $this->moduleName . '/store',
            $this->tokenData['param'],
            true
        );
        $this->params['cancel'] = $this->url->link(
            'extension/module/' . $this->moduleName,
            $this->tokenData['param'],
            true
        );

        $this->params['header'] = $this->load->controller('common/header');
        $this->params['column_left'] = $this->load->controller('common/column_left');
        $this->params['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->awCore->render(
            'extension/module/' . $this->moduleName . '/setting',
            $this->params
        ));
    }

    public function info()
    {
        if (($this->request->server['REQUEST_METHOD'] === 'POST') && isset($this->request->post['abandoned_id'])) {
            $abandonedId = (int) $this->request->post['abandoned_id'];

            $this->load->model('extension/module/' . $this->moduleName);

            $abandonedOrderInfo = $this->model_extension_module_aw_easy_abandoned->getOrderInfo($abandonedId);

            if ($abandonedOrderInfo) {
                $this->params['customer_info'] = [
                    'abandoned_id' => $abandonedOrderInfo['abandoned_id'],
                    'store_id' => $abandonedOrderInfo['store_id'],
                    'customer_id' => $abandonedOrderInfo['customer_id'],
                    'email' => $abandonedOrderInfo['email'],
                    'telephone' => $abandonedOrderInfo['telephone'],
                    'created_at' => date(
                        $this->language->get('datetime_format'),
                        strtotime($abandonedOrderInfo['created_at'])
                    ),
                    'customer' => $abandonedOrderInfo['firstname'] . ' ' . $abandonedOrderInfo['lastname'],
                ];

                $this->params['orders'] = [];

                if (! empty($this->params['customer_info']['email']) && ! empty($this->params['customer_info']['telephone'])) {
                    $orders = $this->model_extension_module_aw_easy_abandoned->getOrdersByCustomerData($this->params['customer_info']);

                    foreach ($orders as $order) {
                        $this->params['orders'][] = [
                            'order_id' => $order['order_id'],
                            'customer' => $order['customer'],
                            'email' => $order['email'],
                            'telephone' => $order['telephone'],
                            'total' => $this->currency->format(
                                $order['total'],
                                $order['currency_code'],
                                $order['currency_value']
                            ),
                            'created_at' => date(
                                $this->language->get('datetime_format'),
                                strtotime($order['date_added'])
                            ),
                        ];
                    }
                }

                $this->params['products'] = [];

                $products = json_decode($abandonedOrderInfo['products'], true);

                if (! empty($products)) {
                    foreach ($products as $product) {
                        $options = [];

                        if ($product['option']) {
                            foreach ($product['option'] as $option) {
                                $options[] = [
                                    'name' => $option['name'],
                                    'value' => $option['value'],
                                ];
                            }
                        }

                        $this->params['products'][] = [
                            'product_id' => $product['product_id'],
                            'name' => $product['name'],
                            'model' => $product['model'],
                            'quantity' => $product['quantity'],
                            'option' => $options,
                            'price' => $product['price'],
                            'total' => $product['total'],
                            'href' => HTTP_CATALOG . 'index.php?route=product/product&product_id=' . $product['product_id'],
                        ];
                    }
                }

                $this->response->setOutput($this->awCore->render(
                    'extension/module/' . $this->moduleName . '/order_info',
                    $this->params
                ));
            }
        }
    }

    public function widget()
    {
        $this->load->language('extension/module/' . $this->moduleName);
        $this->load->model('extension/module/' . $this->moduleName);

        $this->params['status'] = $this->moduleConfig->get('status');

        $tableExists = $this->model_extension_module_aw_easy_abandoned->isTableExists();

        if (!$tableExists) {
            $this->params['status'] = false;
        }

        $this->params['order_count'] = $this->model_extension_module_aw_easy_abandoned->getTotalCountOrder();
        $this->params['link'] = $this->url->link(
            'extension/module/' . $this->moduleName,
            $this->tokenData['param'],
            true
        );

        return $this->awCore->render('extension/module/' . $this->moduleName . '/widget', $this->params);
    }

    public function store()
    {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

            $this->awCore->setConfig($this->moduleName, $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success_save_setting');

            $this->response->redirect($this->url->link(
                'extension/module/' . $this->moduleName,
                $this->tokenData['param'] . '&type=module',
                true
            ));
        }

        $this->index();
    }

    public function delete()
    {
        $this->load->model('extension/module/' . $this->moduleName);

        if ((($this->request->server['REQUEST_METHOD'] === 'POST') && isset($this->request->post['selected'])) || (($this->request->server['REQUEST_METHOD'] === 'GET') && isset($this->request->get['abandoned_id']))) {
            $abandonedOrders = [];

            if ($this->request->server['REQUEST_METHOD'] === 'POST') {
                $abandonedOrders = $this->request->post['selected'];
            } elseif ($this->request->server['REQUEST_METHOD'] === 'GET') {
                $abandonedOrders[] = (int) $this->request->get['abandoned_id'];
            }

            if ($this->validate()) {
                foreach ($abandonedOrders as $abandonedId) {
                    $this->model_extension_module_aw_easy_abandoned->deleteOrder($abandonedId);
                }

                $this->session->data['success'] = $this->language->get('text_success');

                $url = '';

                if (isset($this->request->get['filter_abandoned_id'])) {
                    $url .= '&filter_abandoned_id=' . $this->request->get['filter_abandoned_id'];
                }

                if (isset($this->request->get['filter_customer'])) {
                    $url .= '&filter_customer=' . urlencode(html_entity_decode(
                        $this->request->get['filter_customer'],
                        ENT_QUOTES,
                        'UTF-8'
                    ));
                }

                if (isset($this->request->get['filter_created_at'])) {
                    $url .= '&filter_created_at=' . $this->request->get['filter_created_at'];
                }

                $this->response->redirect($this->url->link(
                    'extension/module/' . $this->moduleName,
                    $this->tokenData['param'] . $url,
                    true
                ));
            }
        }

        $this->getList();
    }

    public function sendEmailMessage()
    {
        $json = [];

        $this->load->model('extension/module/' . $this->moduleName);

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $abandonedId = $this->request->post['abandoned_id'];

            $orderInfo = $this->model_extension_module_aw_easy_abandoned->getOrderInfo($abandonedId);

            $this->params['order_info'] = [];

            if ($orderInfo) {
                if (!empty($orderInfo['email_sent_at'])) {
                    $json['error'] = sprintf($this->language->get('text_error_email_already_sent'), $abandonedId);
                }

                if (! empty($orderInfo['products'])) {
                    $products = json_decode($orderInfo['products'], true);
                } else {
                    $products = [];
                }

                $this->params['order_info'] = [
                    'email' => $orderInfo['email'],
                    'telephone' => $orderInfo['telephone'],
                    'firstname' => $orderInfo['firstname'],
                    'lastname' => $orderInfo['lastname'],
                    'language_id' => $orderInfo['language_id'],
                    'products' => $products,
                    'created_at' => date($this->language->get('datetime_format'), strtotime($orderInfo['created_at'])),
                ];
            }

            $storeName = $this->config->get('config_name');
            $emailSubject = $this->moduleConfig->get('email_subject');

            $subject = '';

            if (! empty($emailSubject[$orderInfo['language_id']])) {
                $subject = $this->getCustomFields(
                    $this->params['order_info'],
                    $emailSubject[$orderInfo['language_id']]
                );
            } else {
                $json['error'] = $this->language->get('text_error_email_subject');
            }

            $html = '';

            $emailTemplate = $this->moduleConfig->get('email_template');

            if (! empty($emailTemplate[$orderInfo['language_id']])) {
                $html = $this->getCustomFields($this->params['order_info'], $emailTemplate[$orderInfo['language_id']]);
            } else {
                $json['error'] = $this->language->get('text_error_email_template');
            }

            if (
                isset($orderInfo['email']) && filter_var($orderInfo['email'], FILTER_VALIDATE_EMAIL)
            ) {
                $email = $orderInfo['email'];
            } else {
                $json['error'] = $this->language->get('text_error_email');
            }

            if (! $json) {

                $mail = new Mail($this->config->get('config_mail_engine'));
                $mail->parameter = $this->config->get('config_mail_parameter');
                $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                $mail->smtp_port = $this->config->get('config_mail_smtp_port');
                $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

                $mail->setTo($email);
                $mail->setFrom($this->config->get('config_email'));
                $mail->setSender(html_entity_decode($storeName, ENT_QUOTES, 'UTF-8'));
                $mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
                $mail->setHtml(html_entity_decode($html, ENT_QUOTES, 'UTF-8'));
                $mail->send();

                $emailSentAt = $this->model_extension_module_aw_easy_abandoned->addStatusSendEmail($abandonedId);

                $json['email_sent_at'] = $this->language->get('text_send_message') . date(
                    $this->language->get('datetime_format'),
                    strtotime($emailSentAt)
                );

                $json['success'] = $this->language->get('text_success_send_email_message');
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function sendSmsMessage()
    {
        $json = [];

        $this->load->model('extension/module/' . $this->moduleName);

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $abandonedId = $this->request->post['abandoned_id'];

            $orderInfo = $this->model_extension_module_aw_easy_abandoned->getOrderInfo($abandonedId);

            $this->params['order_info'] = [];

            if ($orderInfo) {
                if (!empty($orderInfo['sms_sent_at'])) {
                    $json['error'] = sprintf($this->language->get('text_error_sms_already_sent'), $abandonedId);
                }

                if (! empty($orderInfo['products'])) {
                    $products = json_decode($orderInfo['products'], true);
                } else {
                    $products = [];
                }

                $this->params['order_info'] = [
                    'email' => $orderInfo['email'],
                    'telephone' => $orderInfo['telephone'],
                    'firstname' => $orderInfo['firstname'],
                    'lastname' => $orderInfo['lastname'],
                    'language_id' => $orderInfo['language_id'],
                    'products' => $products,
                    'created_at' => date($this->language->get('datetime_format'), strtotime($orderInfo['created_at'])),
                ];
            }

            $smsTemplate = $this->moduleConfig->get('sms_template');

            $message = '';

            if (! empty($smsTemplate[$orderInfo['language_id']])) {
                $message = $this->getCustomFields($this->params['order_info'], $smsTemplate[$orderInfo['language_id']]);
            } else {
                $json['error'] = $this->language->get('text_error_sms_template');
            }

            if (! empty($orderInfo['telephone'])) {
                $telephone = $orderInfo['telephone'];
            } else {
                $json['error'] = $this->language->get('text_error_telephone');
            }

            if (!file_exists(DIR_APPLICATION . 'controller/extension/module/aw_sms_notify.php')) {
                $json['error'] = $this->language->get('text_error_sms_module_not_installed');
            }

            if (! $json) {
                $this->load->model('setting/setting');
                $smsModuleConfig = $this->awCore->getConfig('aw_sms_notify');

                if (!$smsModuleConfig->get('sms_notify_gatename') || !$smsModuleConfig->get('sms_notify_gate_username')) {
                    $json['error'] = $this->language->get('text_error_sms_module_not_installed');
                } else {
                    $phone = $this->awCore->prepareNumber($telephone);

                    $options = [
                        'to' => $phone,
                        'from' => $smsModuleConfig->get('sms_notify_from'),
                        'username' => $smsModuleConfig->get('sms_notify_gate_username'),
                        'password' => $smsModuleConfig->get('sms_notify_gate_password'),
                        'message' => strip_tags($message),
                        'viber' => [
                            'status' => false,
                        ],
                    ];

                    $dispatcher = new \Alexwaha\SmsDispatcher(
                        $smsModuleConfig->get('sms_notify_gatename'),
                        $options,
                        $smsModuleConfig->get('sms_notify_log_filename')
                    );
                    $dispatcher->send();

                    $smsSentAt = $this->model_extension_module_aw_easy_abandoned->addStatusSendSms($abandonedId);

                    $json['sms_sent_at'] = $this->language->get('text_send_sms_message') . date(
                        $this->language->get('datetime_format'),
                        strtotime($smsSentAt)
                    );

                    $json['success'] = $this->language->get('text_success_send_sms_message');
                }
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    protected function validate(): bool
    {
        if (! $this->user->hasPermission('modify', 'extension/module/' . $this->moduleName)) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return ! $this->error;
    }

    public function autocomplete()
    {
        $json = [];

        if (isset($this->request->get['filter_name'])) {
            if (isset($this->request->get['filter_name'])) {
                $filter_name = $this->request->get['filter_name'];
            } else {
                $filter_name = '';
            }

            $this->load->model('extension/module/' . $this->moduleName);

            $filterData = [
                'filter_name' => $filter_name,
                'start' => 0,
                'limit' => 10,
            ];
            $results = $this->model_extension_module_aw_easy_abandoned->getCustomers($filterData);

            foreach ($results as $result) {
                $json[] = [
                    'name' => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
                    'firstname' => $result['firstname'],
                    'lastname' => $result['lastname'],
                    'email' => $result['email'],
                    'telephone' => $result['telephone'],
                ];
            }
        }

        $sort_order = [];

        foreach ($json as $key => $value) {
            $sort_order[$key] = $value['name'];
        }

        array_multisort($sort_order, SORT_ASC, $json);
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}

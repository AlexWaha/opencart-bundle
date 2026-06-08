<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionModuleAwBuyerHistory extends Controller
{
    private string $moduleName = 'aw_buyer_history';
    private \Alexwaha\Config $moduleConfig;
    private \Alexwaha\Language $language;
    private array $error = [];
    private string $routeExtension;
    private array $params;
    private array $tokenData;

    private const PRESETS_SECONDS = [
        '1h' => 3600,
        '3h' => 10800,
        '6h' => 21600,
        '12h' => 43200,
        '24h' => 86400,
        '48h' => 172800,
        '72h' => 259200,
        '7d' => 604800,
    ];


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

    public function index(): void
    {
        $this->document->setTitle($this->language->get('heading_main_title'));
        $this->awCore->addStyles();
        $this->document->addStyle('view/stylesheet/aw_buyer_history/style.css');
        $this->document->addStyle('view/javascript/aw_buyer_history/coloris.min.css');
        $this->document->addScript('view/javascript/aw_buyer_history/coloris.min.js');

        $this->load->model('localisation/order_status');

        $this->params['success'] = $this->session->data['success'] ?? '';
        unset($this->session->data['success']);

        $this->params['error'] = $this->error;
        $this->params['module_name'] = $this->moduleName;

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

        $this->params['action'] = $this->url->link('extension/module/' . $this->moduleName . '/store', $this->tokenData['param'], true);
        $this->params['cancel'] = $this->url->link($this->routeExtension, $this->tokenData['param'] . '&type=module', true);
        $this->params['export_url'] = $this->url->link('extension/module/' . $this->moduleName . '/exportConfig', $this->tokenData['param'], true);
        $this->params['import_url'] = $this->url->link('extension/module/' . $this->moduleName . '/importConfig', $this->tokenData['param'], true);

        $this->load->model('localisation/order_status');
        $allStatuses = $this->model_localisation_order_status->getOrderStatuses();
        $allStatusIds = array_map(static fn ($s) => (int) $s['order_status_id'], $allStatuses);

        $this->params['status'] = (bool) $this->moduleConfig->get('status', false);
        $this->params['match_guests'] = (bool) $this->moduleConfig->get('match_guests', true);

        $tracked = $this->moduleConfig->get('tracked_status_ids', null);
        $this->params['tracked_status_ids'] = is_array($tracked) ? array_map('intval', $tracked) : $allStatusIds;

        $this->params['threshold_mid'] = (int) $this->moduleConfig->get('threshold_mid', 3);
        $this->params['threshold_high'] = (int) $this->moduleConfig->get('threshold_high', 10);
        $this->params['color_low_bg']    = (string) $this->moduleConfig->get('color_low_bg', '#5bc0de');
        $this->params['color_low_text']  = (string) $this->moduleConfig->get('color_low_text', '#ffffff');
        $this->params['color_mid_bg']    = (string) $this->moduleConfig->get('color_mid_bg', '#5cb85c');
        $this->params['color_mid_text']  = (string) $this->moduleConfig->get('color_mid_text', '#ffffff');
        $this->params['color_high_bg']   = (string) $this->moduleConfig->get('color_high_bg', '#f0ad4e');
        $this->params['color_high_text'] = (string) $this->moduleConfig->get('color_high_text', '#ffffff');

        $this->params['duplicates_enabled'] = (bool) $this->moduleConfig->get('duplicates_enabled', true);
        $this->params['duplicate_window_preset'] = (string) $this->moduleConfig->get('duplicate_window_preset', '24h');
        $this->params['duplicate_window_custom_value'] = (int) $this->moduleConfig->get('duplicate_window_custom_value', 24);
        $this->params['duplicate_window_custom_unit'] = (string) $this->moduleConfig->get('duplicate_window_custom_unit', 'hours');
        $this->params['duplicate_min_count'] = (int) $this->moduleConfig->get('duplicate_min_count', 2);
        $this->params['color_dup_bg']   = (string) $this->moduleConfig->get('color_dup_bg', '#d9534f');
        $this->params['color_dup_text'] = (string) $this->moduleConfig->get('color_dup_text', '#ffffff');
        $this->params['duplicate_link_target'] = (string) $this->moduleConfig->get('duplicate_link_target', '_blank');
        $this->params['duplicate_max_shown'] = (int) $this->moduleConfig->get('duplicate_max_shown', 5);

        $this->params['show_history_column'] = (bool) $this->moduleConfig->get('show_history_column', true);
        $this->params['show_duplicates_column'] = (bool) $this->moduleConfig->get('show_duplicates_column', true);

        $this->params['order_statuses'] = $allStatuses;
        $this->params['status_colors'] = (array) $this->moduleConfig->get('status_colors', []);

        $this->params['header'] = $this->load->controller('common/header');
        $this->params['column_left'] = $this->load->controller('common/column_left');
        $this->params['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->awCore->render('extension/module/' . $this->moduleName . '/main', $this->params));
    }

    public function store(): void
    {
        $this->load->language('extension/module/' . $this->moduleName);

        $json = ['success' => false];

        if (! $this->validate()) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            $this->awCore->setConfig($this->moduleName, $this->request->post);
            $json['success'] = true;
            $json['text'] = $this->language->get('text_success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function exportConfig(): void
    {
        if (! $this->validate()) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode(['error' => $this->language->get('error_permission')]));

            return;
        }

        try {
            $jsonData = $this->awCore->exportConfig($this->moduleName);
            $filename = $this->moduleName . '_settings_' . date('Y-m-d_H-i-s') . '.json';

            $this->response->addHeader('Content-Type: application/json');
            $this->response->addHeader('Content-Disposition: attachment; filename="' . $filename . '"');
            $this->response->setOutput($jsonData);
        } catch (\Exception $e) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode(['error' => sprintf($this->language->get('error_import_failed'), $e->getMessage())]));
        }
    }

    public function importConfig(): void
    {
        $json = [];

        if (! $this->validate()) {
            $json['error'] = $this->language->get('error_permission');
        } elseif (isset($this->request->files['import_file']) && is_uploaded_file($this->request->files['import_file']['tmp_name'])) {
            try {
                $fileContent = file_get_contents($this->request->files['import_file']['tmp_name']);

                if ($fileContent === false) {
                    throw new \Exception($this->language->get('error_import_read_file'));
                }

                $this->awCore->importConfig($this->moduleName, $fileContent);
                $json['success'] = $this->language->get('text_import_success');
            } catch (\Exception $e) {
                $json['error'] = sprintf($this->language->get('error_import_failed'), $e->getMessage());
            }
        } else {
            $json['error'] = $this->language->get('error_import_file');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function validate(): bool
    {
        if (! $this->user->hasPermission('modify', 'extension/module/' . $this->moduleName)) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return ! $this->error;
    }

    public function install(): void
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('module_' . $this->moduleName, ['module_' . $this->moduleName . '_status' => '1']);

        $this->installEvents();
        $this->installPermissions();

        $this->load->model('extension/module/' . $this->moduleName);
        $this->model_extension_module_aw_buyer_history->createIndexes();
    }

    public function uninstall(): void
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('module_' . $this->moduleName);
        $this->awCore->removeConfig($this->moduleName);

        $this->load->model('extension/module/' . $this->moduleName);
        $this->model_extension_module_aw_buyer_history->dropIndexes();

        if ($this->awCore->isLegacy()) {
            $this->load->model('extension/event');
            $this->model_extension_event->deleteEvent('aw_buyer_history_list');
            $this->model_extension_event->deleteEvent('aw_buyer_history_menu');
        } else {
            $this->load->model('setting/event');
            $this->model_setting_event->deleteEventByCode('aw_buyer_history_list');
            $this->model_setting_event->deleteEventByCode('aw_buyer_history_menu');
        }
    }

    protected function installEvents(): void
    {
        if ($this->awCore->isLegacy()) {
            $this->load->model('extension/event');
            $model = 'model_extension_event';
        } else {
            $this->load->model('setting/event');
            $model = 'model_setting_event';
        }

        if ($this->awCore->isLegacy()) {
            $this->{$model}->deleteEvent('aw_buyer_history_list');
        } else {
            $this->{$model}->deleteEventByCode('aw_buyer_history_list');
        }

        $this->{$model}->addEvent(
            'aw_buyer_history_list',
            'admin/view/sale/order_list/before',
            'extension/module/aw_buyer_history/onOrderListView',
            1
        );

        if ($this->awCore->isLegacy()) {
            $this->{$model}->deleteEvent('aw_buyer_history_menu');
        } else {
            $this->{$model}->deleteEventByCode('aw_buyer_history_menu');
        }

        $this->{$model}->addEvent(
            'aw_buyer_history_menu',
            'admin/view/common/column_left/before',
            'extension/module/aw_buyer_history/injectReportsMenuItem',
            1
        );
    }

    protected function installPermissions(): void
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

    public function onOrderListView(&$route, &$data, &$code): void
    {
        if ($route !== 'sale/order_list') {
            return;
        }

        if (! isset($data['orders']) || ! is_array($data['orders']) || ! $data['orders']) {
            return;
        }

        $config = $this->awCore->getConfig($this->moduleName);

        if (! $config->get('status', false)) {
            return;
        }

        $showHistory = (bool) $config->get('show_history_column', true);
        $showDuplicates = (bool) $config->get('show_duplicates_column', true);
        $duplicatesEnabled = (bool) $config->get('duplicates_enabled', true);

        if (! $showHistory && ! $showDuplicates) {
            return;
        }

        $this->load->model('extension/module/' . $this->moduleName);
        $model = $this->model_extension_module_aw_buyer_history;

        $orderIds = array_map(function ($row) {
            return (int) ($row['order_id'] ?? 0);
        }, $data['orders']);

        $meta = $model->getOrdersMeta($orderIds);

        if (! $meta) {
            return;
        }

        $matchGuests = (bool) $config->get('match_guests', true);

        $trackedStatusIds = $config->get('tracked_status_ids', null);
        $trackedStatusIds = is_array($trackedStatusIds) ? array_map('intval', $trackedStatusIds) : null;

        $thresholdMid = max(1, (int) $config->get('threshold_mid', 3));
        $thresholdHigh = max(1, (int) $config->get('threshold_high', 10));

        $tierColors = [
            'low'  => ['bg' => (string) $config->get('color_low_bg',  '#5bc0de'), 'fg' => (string) $config->get('color_low_text',  '#ffffff')],
            'mid'  => ['bg' => (string) $config->get('color_mid_bg',  '#5cb85c'), 'fg' => (string) $config->get('color_mid_text',  '#ffffff')],
            'high' => ['bg' => (string) $config->get('color_high_bg', '#f0ad4e'), 'fg' => (string) $config->get('color_high_text', '#ffffff')],
        ];

        $dupBg = (string) $config->get('color_dup_bg',   '#d9534f');
        $dupFg = (string) $config->get('color_dup_text', '#ffffff');
        $dupTarget = (string) $config->get('duplicate_link_target', '_blank');
        $dupMaxShown = max(1, (int) $config->get('duplicate_max_shown', 5));
        $dupMinCount = max(2, (int) $config->get('duplicate_min_count', 2));
        $dupWindowSec = $this->resolveWindowSeconds($config);

        $rowKeys = [];
        $allCids = [];
        $allEmails = [];
        $allPhones = [];

        foreach ($meta as $orderId => $m) {
            $key = $this->buildMatchKey($m, $matchGuests);
            $rowKeys[$orderId] = $key;

            if ((int) $m['customer_id'] > 0) {
                $allCids[] = (int) $m['customer_id'];
            } elseif ($matchGuests) {
                $allEmails[] = strtolower(trim($m['email']));
                $allPhones[] = preg_replace('/\D+/', '', $m['telephone']);
            }
        }

        $statsByKey = [];

        if ($showHistory) {
            $statsRows = $model->getCustomerStats($allCids, $allEmails, $allPhones);

            foreach ($statsRows as $sr) {
                $rowKey = $this->buildMatchKey([
                    'customer_id' => $sr['customer_id'],
                    'email' => $sr['email_norm'],
                    'telephone' => $sr['phone_raw'],
                    'order_id' => 0,
                ], $matchGuests);

                if ($trackedStatusIds !== null && ! in_array((int) $sr['order_status_id'], $trackedStatusIds, true)) {
                    continue;
                }

                $statsByKey[$rowKey][] = $sr;
            }
        }

        $duplicatesByKey = [];

        if ($showDuplicates && $duplicatesEnabled) {
            $minTs = PHP_INT_MAX;
            $maxTs = 0;

            foreach ($meta as $m) {
                $ts = strtotime($m['date_added']);

                if ($ts === false) {
                    continue;
                }

                $minTs = min($minTs, $ts);
                $maxTs = max($maxTs, $ts);
            }

            if ($maxTs > 0) {
                $startTs = $minTs - $dupWindowSec;
                $endTs = $maxTs + $dupWindowSec;

                $dupRows = $model->getDuplicates($allCids, $allEmails, $startTs, $endTs, []);

                foreach ($dupRows as $dr) {
                    $key = $this->buildMatchKey([
                        'customer_id' => $dr['customer_id'],
                        'email' => $dr['email_norm'],
                        'telephone' => $dr['phone_raw'],
                        'order_id' => 0,
                    ], $matchGuests);

                    $duplicatesByKey[$key][] = [
                        'order_id' => (int) $dr['order_id'],
                        'date_added' => $dr['date_added'],
                        'ts' => strtotime($dr['date_added']),
                    ];
                }
            }
        }

        $baseCurrency = $this->config->get('config_currency');

        $tooltipTotalLabel = $this->language->get('text_tooltip_total');
        $breakdownLabel = $this->language->get('text_tooltip_breakdown');

        foreach ($data['orders'] as &$row) {
            $orderId = (int) ($row['order_id'] ?? 0);

            if (! isset($meta[$orderId])) {
                continue;
            }

            $key = $rowKeys[$orderId];

            $totalCount = 0;
            $sumsByCurrency = [];
            $byStatus = [];

            if ($showHistory && isset($statsByKey[$key])) {
                foreach ($statsByKey[$key] as $sr) {
                    $cnt = (int) $sr['cnt'];
                    $baseTotal = (float) $sr['sum_total'];

                    $totalCount += $cnt;
                    $sumsByCurrency[$baseCurrency] = ($sumsByCurrency[$baseCurrency] ?? 0) + $baseTotal;

                    $statusKey = (int) $sr['order_status_id'];

                    if (! isset($byStatus[$statusKey])) {
                        $byStatus[$statusKey] = [
                            'name' => $sr['status_name'] ?: ('#' . $statusKey),
                            'count' => 0,
                            'sums' => [],
                        ];
                    }

                    $byStatus[$statusKey]['count'] += $cnt;
                    $byStatus[$statusKey]['sums'][$baseCurrency] = ($byStatus[$statusKey]['sums'][$baseCurrency] ?? 0) + $baseTotal;
                }
            }

            if ($totalCount >= $thresholdHigh) {
                $tier = 'high';
            } elseif ($totalCount >= $thresholdMid) {
                $tier = 'mid';
            } else {
                $tier = 'low';
            }

            $tierBg = $tierColors[$tier]['bg'];
            $tierFg = $tierColors[$tier]['fg'];

            $label = (string) $totalCount;

            $duplicatesShown = [];
            $overflowItems = [];

            if ($showDuplicates && $duplicatesEnabled) {
                $rowTs = strtotime($meta[$orderId]['date_added']);
                $candidates = $duplicatesByKey[$key] ?? [];

                $matched = [];

                foreach ($candidates as $c) {
                    if ((int) $c['order_id'] === $orderId) {
                        continue;
                    }

                    if ($rowTs && $c['ts'] && abs($c['ts'] - $rowTs) <= $dupWindowSec) {
                        $matched[] = $c;
                    }
                }

                if (count($matched) + 1 >= $dupMinCount) {
                    $visible = array_slice($matched, 0, $dupMaxShown);
                    $hidden = array_slice($matched, $dupMaxShown);

                    foreach ($visible as $m) {
                        $duplicatesShown[] = [
                            'order_id' => (int) $m['order_id'],
                            'url' => $this->url->link('sale/order/info', $this->tokenData['param'] . '&order_id=' . $m['order_id'], true),
                        ];
                    }

                    foreach ($hidden as $m) {
                        $overflowItems[] = [
                            'order_id' => (int) $m['order_id'],
                            'url' => $this->url->link('sale/order/info', $this->tokenData['param'] . '&order_id=' . $m['order_id'], true),
                        ];
                    }
                }
            }

            $row['aw_history'] = [
                'show_history' => $showHistory,
                'show_duplicates' => $showDuplicates && $duplicatesEnabled,
                'history_html' => $showHistory ? $this->renderHistoryCell($label, $tierBg, $tierFg, $totalCount, $sumsByCurrency, $byStatus, $tooltipTotalLabel, $breakdownLabel) : '',
                'duplicates_html' => ($showDuplicates && $duplicatesEnabled) ? $this->renderDuplicatesCell($duplicatesShown, $overflowItems, $dupBg, $dupFg, $dupTarget) : '',
                'label_history' => $this->language->get('column_history'),
                'label_duplicates' => $this->language->get('column_duplicates'),
            ];
        }

        unset($row);

        if (! $this->awCore->isLegacy()) {
            $this->injectOrderListTemplate($code);
        }
    }

    private function injectOrderListTemplate(&$code): void
    {
        $templateFile = DIR_TEMPLATE . 'sale/order_list.twig';

        if (! is_file($templateFile)) {
            return;
        }

        $source = $code !== '' ? $code : file_get_contents($templateFile);

        if ($source === false || $source === '') {
            return;
        }

        $headerNeedle = '<a href="{{ sort_customer }}">{{ column_customer }}</a> {% endif %}</td>';

        if (strpos($source, $headerNeedle) !== false && strpos($source, 'aw_history.label_history') === false) {
            $source = str_replace($headerNeedle, $headerNeedle . "\n" . $this->loadInjector('order_list_headers'), $source);
        }

        $cellNeedle = '<td class="text-left">{{ order.customer }}</td>';

        if (strpos($source, $cellNeedle) !== false && strpos($source, 'aw_history.history_html') === false) {
            $source = str_replace($cellNeedle, $cellNeedle . "\n" . $this->loadInjector('order_list_cells'), $source);
        }

        $cssFile = DIR_APPLICATION . 'view/stylesheet/aw_buyer_history/style.css';

        if (is_file($cssFile)) {
            $styleTag = '<style>' . file_get_contents($cssFile) . '</style>';
            $headerNeedle = '{{ header }}';

            if (strpos($source, $headerNeedle) !== false) {
                $source = str_replace($headerNeedle, $headerNeedle . "\n" . $styleTag, $source);
            } else {
                $source = $source . $styleTag;
            }
        }

        $code = $source;
    }

    private function loadInjector(string $name): string
    {
        $file = DIR_APPLICATION . 'view/template/extension/module/' . $this->moduleName . '/injectors/' . $name . '.twig';

        return is_file($file) ? file_get_contents($file) : '';
    }

    private function renderInjector(string $name, array $data): string
    {
        return $this->awCore->render('extension/module/' . $this->moduleName . '/injectors/' . $name, $data);
    }

    private function renderHistoryCell(string $label, string $bg, string $fg, int $totalCount, array $sumsByCurrency, array $byStatus, string $totalLabel, string $breakdownLabel): string
    {
        foreach ($byStatus as &$sd) {
            $sd['sum_fmt'] = $sd['sums'] ? $this->formatSumsPlain($sd['sums']) : '';
        }
        unset($sd);

        return $this->renderInjector('history_cell', [
            'label' => $label,
            'bg' => $bg,
            'fg' => $fg,
            'total_count' => $totalCount,
            'total_sum_fmt' => $sumsByCurrency ? $this->formatSumsPlain($sumsByCurrency) : '',
            'by_status' => array_values($byStatus),
            'total_label' => $totalLabel,
            'breakdown_label' => $breakdownLabel,
        ]);
    }

    private function renderDuplicatesCell(array $duplicates, array $overflowItems, string $bg, string $fg, string $target): string
    {
        if (! $duplicates && ! $overflowItems) {
            return '';
        }

        return $this->renderInjector('duplicates_cell', [
            'duplicates' => $duplicates,
            'overflow' => $overflowItems,
            'bg' => $bg,
            'fg' => $fg,
            'target' => $target,
        ]);
    }

    private function formatSumsPlain(array $sumsByCurrency): string
    {
        $parts = [];

        foreach ($sumsByCurrency as $code => $amount) {
            $parts[] = trim(strip_tags(html_entity_decode($this->currency->format($amount, $code, 1), ENT_QUOTES, 'UTF-8')));
        }

        return implode(', ', $parts);
    }

    private function buildMatchKey(array $row, bool $matchGuests): string
    {
        $cid = (int) ($row['customer_id'] ?? 0);

        if ($cid > 0) {
            return 'cid:' . $cid;
        }

        if (! $matchGuests) {
            return 'oid:' . (int) ($row['order_id'] ?? 0);
        }

        $email = strtolower(trim((string) ($row['email'] ?? '')));
        $phone = preg_replace('/\D+/', '', (string) ($row['telephone'] ?? ''));

        return 'gst:' . $email . '|' . $phone;
    }

    private function resolveWindowSeconds(\Alexwaha\Config $config): int
    {
        $preset = (string) $config->get('duplicate_window_preset', '24h');

        if (isset(self::PRESETS_SECONDS[$preset])) {
            return self::PRESETS_SECONDS[$preset];
        }

        $value = max(1, (int) $config->get('duplicate_window_custom_value', 24));
        $unit = (string) $config->get('duplicate_window_custom_unit', 'hours');

        $multipliers = ['minutes' => 60, 'hours' => 3600, 'days' => 86400];

        return $value * ($multipliers[$unit] ?? 3600);
    }

    public function injectReportsMenuItem(&$route, &$data, &$code): void
    {
        if ($route !== 'common/column_left') {
            return;
        }

        if (! isset($data['menus']) || ! is_array($data['menus'])) {
            return;
        }

        $config = $this->awCore->getConfig($this->moduleName);

        if (! $config->get('status', false)) {
            return;
        }

        $newItem = [
            'name'     => $this->language->get('report_menu_label'),
            'href'     => $this->url->link('extension/module/' . $this->moduleName . '/report', $this->tokenData['param'], true),
            'children' => [],
        ];

        foreach ($data['menus'] as $i => $menu) {
            if (($menu['id'] ?? '') === 'menu-report') {
                $data['menus'][$i]['children'][] = $newItem;

                return;
            }
        }

        $data['menus'][] = [
            'id'       => 'menu-aw-buyer-history',
            'icon'     => 'fa-users',
            'name'     => $this->language->get('report_menu_label'),
            'href'     => $this->url->link('extension/module/' . $this->moduleName . '/report', $this->tokenData['param'], true),
            'children' => [],
        ];
    }

    public function report(): void
    {
        $this->document->setTitle($this->language->get('report_heading'));
        $this->awCore->addStyles();
        $this->document->addStyle('view/stylesheet/aw_buyer_history/style.css');

        $this->load->model('extension/module/' . $this->moduleName);
        $model = $this->model_extension_module_aw_buyer_history;

        $config = $this->awCore->getConfig($this->moduleName);

        $thresholdMid = max(1, (int) $config->get('threshold_mid', 3));
        $thresholdHigh = max(1, (int) $config->get('threshold_high', 10));

        $tierColors = [
            'low'  => ['bg' => (string) $config->get('color_low_bg',  '#5bc0de'), 'fg' => (string) $config->get('color_low_text',  '#ffffff')],
            'mid'  => ['bg' => (string) $config->get('color_mid_bg',  '#5cb85c'), 'fg' => (string) $config->get('color_mid_text',  '#ffffff')],
            'high' => ['bg' => (string) $config->get('color_high_bg', '#f0ad4e'), 'fg' => (string) $config->get('color_high_text', '#ffffff')],
        ];

        $page = max(1, (int) ($this->request->get['page'] ?? 1));
        $limit = 20;
        $start = ($page - 1) * $limit;

        $sortAllowed = ['total_orders', 'total_amount', 'avg_amount', 'first_order_date', 'last_order_date'];
        $sort = in_array(($this->request->get['sort'] ?? ''), $sortAllowed, true) ? $this->request->get['sort'] : 'last_order_date';
        $order = (($this->request->get['order'] ?? '') === 'ASC') ? 'ASC' : 'DESC';

        $filters = [
            'search' => trim((string) ($this->request->get['filter_search'] ?? '')),
            'tier' => in_array(($this->request->get['filter_tier'] ?? ''), ['low', 'mid', 'high'], true) ? $this->request->get['filter_tier'] : '',
            'has_duplicates' => ! empty($this->request->get['filter_duplicates']),
            'threshold_mid' => $thresholdMid,
            'threshold_high' => $thresholdHigh,
            'tracked_status_ids' => (array) $config->get('tracked_status_ids', []),
            'duplicate_window_seconds' => $this->resolveWindowSeconds($config),
            'duplicate_min_count' => max(2, (int) $config->get('duplicate_min_count', 2)),
            'match_guests' => (bool) $config->get('match_guests', true),
        ];

        $rows = $model->getCustomersList($filters, $start, $limit, $sort, $order);
        $total = $model->getCustomersTotal($filters);

        $baseCurrency = $this->config->get('config_currency');

        foreach ($rows as &$row) {
            $count = (int) $row['total_orders'];
            $tier = $count >= $thresholdHigh ? 'high' : ($count >= $thresholdMid ? 'mid' : 'low');
            $row['tier'] = $tier;
            $row['tier_bg'] = $tierColors[$tier]['bg'];
            $row['tier_fg'] = $tierColors[$tier]['fg'];
            $row['total_amount_fmt'] = $this->currency->format((float) $row['total_amount'], $baseCurrency, 1);
            $row['avg_amount_fmt'] = $this->currency->format((float) $row['avg_amount'], $baseCurrency, 1);
            $row['first_order_fmt'] = $row['first_order_date'] ? date($this->language->get('date_format_short'), strtotime($row['first_order_date'])) : '';
            $row['last_order_fmt'] = $row['last_order_date'] ? date($this->language->get('date_format_short'), strtotime($row['last_order_date'])) : '';
            $row['rows_url'] = $this->url->link('extension/module/' . $this->moduleName . '/reportRows', $this->tokenData['param'] . '&match_key=' . urlencode($row['match_key']), true);
        }
        unset($row);

        $statusColors = (array) $config->get('status_colors', []);
        $statusColors = array_filter($statusColors, fn ($v) => $v !== '');

        $this->params['status_colors'] = $statusColors;
        $this->params['rows'] = $rows;
        $this->params['filter_search'] = $filters['search'];
        $this->params['filter_tier'] = $filters['tier'];
        $this->params['filter_duplicates'] = $filters['has_duplicates'];
        $this->params['sort'] = $sort;
        $this->params['order'] = $order;

        $tokenParam = $this->tokenData['param'];

        $this->params['breadcrumbs'] = [
            ['text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', $tokenParam, true)],
            ['text' => $this->language->get('report_heading'), 'href' => $this->url->link('extension/module/' . $this->moduleName . '/report', $tokenParam, true)],
        ];

        $this->params['settings_url'] = $this->url->link('extension/module/' . $this->moduleName, $tokenParam, true);
        $this->params['action'] = $this->url->link('extension/module/' . $this->moduleName . '/report', $tokenParam, true);

        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link('extension/module/' . $this->moduleName . '/report', $tokenParam . '&page={page}'
            . ($filters['search'] !== '' ? '&filter_search=' . urlencode($filters['search']) : '')
            . ($filters['tier'] !== '' ? '&filter_tier=' . urlencode($filters['tier']) : '')
            . ($filters['has_duplicates'] ? '&filter_duplicates=1' : '')
            . '&sort=' . $sort . '&order=' . $order, true);

        $this->params['pagination'] = $pagination->render();
        $this->params['results'] = sprintf($this->language->get('text_pagination'), ($total ? $start + 1 : 0), min($start + $limit, $total), $total, ceil($total / $limit) ?: 1);
        $this->params['total_count'] = $total;

        $this->params['header'] = $this->load->controller('common/header');
        $this->params['column_left'] = $this->load->controller('common/column_left');
        $this->params['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->awCore->render('extension/module/' . $this->moduleName . '/report', $this->params));
    }

    public function reportRows(): void
    {
        if (! $this->user->hasPermission('access', 'extension/module/' . $this->moduleName)) {
            $this->response->addHeader('Content-Type: text/html; charset=utf-8');
            $this->response->setOutput('<div class="alert alert-danger">' . $this->language->get('error_permission') . '</div>');

            return;
        }

        $matchKey = (string) ($this->request->get['match_key'] ?? '');

        if ($matchKey === '') {
            $this->response->setOutput('');

            return;
        }

        $this->load->model('extension/module/' . $this->moduleName);
        $orders = $this->model_extension_module_aw_buyer_history->getCustomerOrders($matchKey);

        $tokenParam = $this->tokenData['param'];

        $config = $this->awCore->getConfig($this->moduleName);
        $statusColors = (array) $config->get('status_colors', []);
        $statusColors = array_filter($statusColors, fn ($v) => $v !== '');

        foreach ($orders as &$o) {
            $o['view_url'] = $this->url->link('sale/order/info', $tokenParam . '&order_id=' . (int) $o['order_id'], true);
            $o['total_fmt'] = $this->currency->format((float) $o['total'], (string) ($o['currency_code'] ?: $this->config->get('config_currency')), (float) ($o['currency_value'] ?: 1));
            $o['date_fmt'] = $o['date_added'] ? date($this->language->get('datetime_format') ?: 'Y-m-d H:i', strtotime($o['date_added'])) : '';
            $sid = (int) ($o['order_status_id'] ?? 0);
            $o['status_color'] = $statusColors[$sid] ?? '';

            $currencyCode = (string) ($o['currency_code'] ?: $this->config->get('config_currency'));
            $currencyValue = (float) ($o['currency_value'] ?: 1);

            foreach ($o['products'] as &$p) {
                $p['edit_url'] = $this->url->link('catalog/product/edit', $tokenParam . '&product_id=' . (int) $p['product_id'], true);
                $p['line_total_fmt'] = $this->currency->format((float) $p['total'], $currencyCode, $currencyValue);
                $p['price_fmt'] = $this->currency->format((float) $p['price'], $currencyCode, $currencyValue);
            }
            unset($p);
        }
        unset($o);

        $this->params['orders'] = $orders;

        $this->response->addHeader('Content-Type: text/html; charset=utf-8');
        $this->response->setOutput($this->awCore->render('extension/module/' . $this->moduleName . '/report_rows', $this->params));
    }
}

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

        $post = $this->request->post;

        if (isset($post['sendsms'])) {
            $sendsms = (int) $post['sendsms'];
        } else {
            $sendsms = $this->moduleConfig->get('sms_notify_force') ? 1 : 0;
        }

        $admin_order = ! empty($post['admin_order']) ? 1 : 0;

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
        $review_data = $args[1] ?? [];

        $this->load->model('extension/module/' . $this->moduleName);

        $this->model_extension_module_aw_sms_notify->sendReviewsSms($product_id, $review_data);
    }

    /**
     * Storefront endpoint: request a fresh OTP code for a phone number.
     * Generates 6-digit code, sends via configured SMS gateway, stores in session.
     *
     * @return void
     */
    public function otpRequest(): void
    {
        $this->load->language('extension/module/' . $this->moduleName);

        $rawPhone = (string) ($this->request->post['phone'] ?? '');
        $phone = $this->normalizePhone($rawPhone);

        if (!$this->isValidPhone($phone)) {
            $this->respondJson(['error' => $this->language->get('error_otp_invalid_phone')]);

            return;
        }

        $session = $this->getOtpSession();
        $now = time();

        $sessionPhone = (string) ($session['phone'] ?? '');
        $lockoutUntil = (int) ($session['lockout_until'] ?? 0);

        if ($lockoutUntil > $now && $sessionPhone === $phone) {
            $retryAfter = $lockoutUntil - $now;

            $this->respondJson([
                'error'       => $this->language->get('error_otp_lockout'),
                'retry_after' => $retryAfter,
                'lockout'     => true,
            ]);

            return;
        }

        $throttle = (int) $this->moduleConfig->get('otp_resend_throttle', 30);
        $lastRequest = (int) ($session['last_request_at'] ?? 0);

        if ($throttle > 0 && $lastRequest > 0 && ($now - $lastRequest) < $throttle) {
            $retryAfter = $throttle - ($now - $lastRequest);

            $this->respondJson([
                'error'       => sprintf($this->language->get('error_otp_throttle'), $retryAfter),
                'retry_after' => $retryAfter,
            ]);

            return;
        }

        $maxResends = (int) $this->moduleConfig->get('otp_max_resends', 2);
        $resendCount = (int) ($session['resend_count'] ?? 0);
        $wasVerified = !empty($session['verified_phone']);

        if ($sessionPhone !== $phone || $wasVerified) {
            $resendCount = 0;
        } elseif ($lastRequest > 0) {
            $resendCount++;
        }

        if ($maxResends > 0 && $resendCount > $maxResends) {
            $lockoutDuration = (int) $this->moduleConfig->get('otp_lockout_duration', 7200);

            $this->setOtpSession(array_merge($session, [
                'lockout_until' => $now + $lockoutDuration,
            ]));

            $this->respondJson([
                'error'       => $this->language->get('error_otp_lockout'),
                'retry_after' => $lockoutDuration,
                'lockout'     => true,
            ]);

            return;
        }

        $code = $this->generateOtpCode();
        $ttl = (int) $this->moduleConfig->get('otp_code_ttl', 300);

        $message = $this->renderOtpSms($code, $phone);

        if ($message === '') {
            $this->respondJson(['error' => $this->language->get('error_otp_gateway')]);

            return;
        }

        if (!$this->dispatchOtpSms($phone, $message)) {
            $this->respondJson(['error' => $this->language->get('error_otp_gateway')]);

            return;
        }

        $resendsLeft = $maxResends - $resendCount;

        $this->setOtpSession([
            'phone'             => $phone,
            'code'              => $code,
            'expires_at'        => $now + $ttl,
            'attempts'          => 0,
            'last_request_at'   => $now,
            'resend_count'      => $resendCount,
            'lockout_until'     => 0,
            'verified_phone'    => null,
            'verified_at'       => null,
            'verified_token'    => null,
        ]);

        $this->respondJson([
            'success'        => true,
            'ttl'            => $ttl,
            'throttle_until' => $now + $throttle,
            'resends_left'   => $resendsLeft,
        ]);
    }

    /**
     * Storefront endpoint: verify a 6-digit OTP code.
     * On success returns a one-time token that the form must submit back.
     *
     * @return void
     */
    public function otpVerify(): void
    {
        $this->load->language('extension/module/' . $this->moduleName);

        $rawPhone = (string) ($this->request->post['phone'] ?? '');
        $phone = $this->normalizePhone($rawPhone);
        $code = preg_replace('/\D/', '', (string) ($this->request->post['code'] ?? ''));

        $session = $this->getOtpSession();
        $now = time();
        $maxAttempts = (int) $this->moduleConfig->get('otp_max_attempts', 5);

        if (empty($session['phone']) || empty($session['code']) || $session['phone'] !== $phone) {
            $this->respondJson(['error' => $this->language->get('error_otp_required')]);

            return;
        }

        if ((int) ($session['expires_at'] ?? 0) < $now) {
            $this->clearOtpSession();
            $this->respondJson(['error' => $this->language->get('error_otp_expired')]);

            return;
        }

        $attempts = (int) ($session['attempts'] ?? 0) + 1;

        if ($maxAttempts > 0 && $attempts > $maxAttempts) {
            $this->clearOtpSession();
            $this->respondJson(['error' => $this->language->get('error_otp_attempts_exceeded')]);

            return;
        }

        if (!hash_equals((string) $session['code'], (string) $code)) {
            $session['attempts'] = $attempts;
            $this->setOtpSession($session);

            $attemptsLeft = max(0, $maxAttempts - $attempts);

            $this->respondJson([
                'error'          => $this->language->get('error_otp_invalid_code'),
                'attempts_left'  => $attemptsLeft,
            ]);

            return;
        }

        $token = hash('sha256', $phone . '|' . bin2hex(random_bytes(16)) . '|' . $now);

        $session['attempts'] = $attempts;
        $session['code'] = null;
        $session['verified_phone'] = $phone;
        $session['verified_at'] = $now;
        $session['verified_token'] = $token;

        $this->setOtpSession($session);

        $this->respondJson([
            'success' => true,
            'token'   => $token,
        ]);
    }

    /**
     * Storefront endpoint: report current OTP state for the active session.
     *
     * @return void
     */
    public function otpStatus(): void
    {
        $session = $this->getOtpSession();
        $now = time();
        $maxAttempts = (int) $this->moduleConfig->get('otp_max_attempts', 5);
        $expiresAt = (int) ($session['expires_at'] ?? 0);
        $verified = !empty($session['verified_token'])
            && !empty($session['verified_phone'])
            && ($expiresAt === 0 || $expiresAt >= $now);

        $this->respondJson([
            'verified'      => $verified,
            'phone'         => (string) ($session['verified_phone'] ?? $session['phone'] ?? ''),
            'expires_at'    => $expiresAt,
            'attempts_left' => max(0, $maxAttempts - (int) ($session['attempts'] ?? 0)),
        ]);
    }

    /**
     * Event: catalog/controller/account/register/before.
     * Router triggers with 2 args (&$route, &$data); $output is optional and may be missing.
     *
     * @param  string     $route
     * @param  array      $args
     * @param  mixed|null $output
     * @return mixed|null  non-null return short-circuits the controller call
     */
    public function enforceOtpRegister(&$route, &$args, &$output = null)
    {
        if (!$this->isOtpEnabled() || !(bool) $this->moduleConfig->get('otp_protect_register', false)) {
            return null;
        }

        if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
            return null;
        }

        $phone = (string) ($this->request->post['telephone'] ?? '');
        $token = (string) ($this->request->post['aw_otp_token'] ?? '');

        if ($this->isOtpValid($phone, $token)) {
            return null;
        }

        return $this->buildGateBlockResponse();
    }

    /**
     * Event: catalog/controller/checkout/guest/save/before.
     *
     * @param  string     $route
     * @param  array      $args
     * @param  mixed|null $output
     * @return mixed|null
     */
    public function enforceOtpCheckoutStd(&$route, &$args, &$output = null)
    {
        if (!$this->isOtpEnabled() || !(bool) $this->moduleConfig->get('otp_protect_checkout_std', false)) {
            return null;
        }

        if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
            return null;
        }

        $phone = (string) ($this->request->post['telephone'] ?? '');
        $token = (string) ($this->request->post['aw_otp_token'] ?? '');

        if ($this->isOtpValid($phone, $token)) {
            return null;
        }

        return $this->buildGateBlockResponse();
    }

    /**
     * Event: catalog/controller/extension/aw_easy_checkout/validation/before.
     *
     * @param  string     $route
     * @param  array      $args
     * @param  mixed|null $output
     * @return mixed|null
     */
    public function enforceOtpCheckoutEasy(&$route, &$args, &$output = null)
    {
        if (!$this->isOtpEnabled() || !(bool) $this->moduleConfig->get('otp_protect_checkout_easy', false)) {
            return null;
        }

        if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
            return null;
        }

        $phone = (string) ($this->request->post['telephone'] ?? '');
        $token = (string) ($this->request->post['aw_otp_token'] ?? '');

        if ($this->isOtpValid($phone, $token)) {
            return null;
        }

        return $this->buildGateBlockResponse();
    }

    /**
     * Event: catalog/model/checkout/order/addOrder/before.
     * Universal model-level gate. Throws to abort addOrder.
     *
     * @param  string $route
     * @param  array  $args
     * @param  mixed  $output
     * @return void
     *
     * @throws \Exception
     */
    public function enforceOtpAddOrder(&$route, &$args, &$output): void
    {
        if (!$this->isOtpEnabled() || !(bool) $this->moduleConfig->get('otp_protect_universal', false)) {
            return;
        }

        $orderData = $args[0] ?? [];
        $phone = (string) ($orderData['telephone'] ?? '');
        $token = (string) ($orderData['aw_otp_token'] ?? ($this->request->post['aw_otp_token'] ?? ''));

        if ($this->isOtpValid($phone, $token)) {
            return;
        }

        throw new \Exception('OTP required');
    }

    /**
     * Event: catalog/model/account/customer/addCustomer/before.
     * Universal model-level gate. Throws to abort addCustomer.
     *
     * @param  string $route
     * @param  array  $args
     * @param  mixed  $output
     * @return void
     *
     * @throws \Exception
     */
    public function enforceOtpAddCustomer(&$route, &$args, &$output): void
    {
        if (!$this->isOtpEnabled() || !(bool) $this->moduleConfig->get('otp_protect_universal', false)) {
            return;
        }

        $customerData = $args[0] ?? [];
        $phone = (string) ($customerData['telephone'] ?? '');
        $token = (string) ($customerData['aw_otp_token'] ?? ($this->request->post['aw_otp_token'] ?? ''));

        if ($this->isOtpValid($phone, $token)) {
            return;
        }

        throw new \Exception('OTP required');
    }

    /**
     * Event: catalog/view/common/footer/after.
     *
     * Injects the OTP modal HTML, CSS link and JS script tag right before </body>.
     * Skipped when the OTP feature is globally disabled, when no protection scope
     * is enabled, when the request is an AJAX call, or when the storefront has
     * not produced a normal HTML response.
     *
     * @param  string $route
     * @param  array  $data
     * @param  string $output
     * @return void
     */
    public function injectOtpAssets(&$route, &$data, &$output): void
    {
        if (!$this->isOtpEnabled()) {
            return;
        }

        $protectRegister = (bool) $this->moduleConfig->get('otp_protect_register', false);
        $protectStd = (bool) $this->moduleConfig->get('otp_protect_checkout_std', false);
        $protectEasy = (bool) $this->moduleConfig->get('otp_protect_checkout_easy', false);

        if (!$protectRegister && !$protectStd && !$protectEasy) {
            return;
        }

        if (!is_string($output) || $output === '' || strpos($output, '</body>') === false) {
            return;
        }

        if ($this->isAjaxRequest()) {
            return;
        }

        $this->load->language('extension/module/' . $this->moduleName);

        $languageId = (int) $this->config->get('config_language_id');
        $titles = (array) $this->moduleConfig->get('otp_modal_title', []);
        $texts = (array) $this->moduleConfig->get('otp_modal_text', []);

        $modalTitle = trim((string) ($titles[$languageId] ?? ''));
        if ($modalTitle === '') {
            $modalTitle = (string) $this->language->get('text_otp_modal_title');
        }

        $modalText = trim((string) ($texts[$languageId] ?? ''));
        if ($modalText === '') {
            $modalText = (string) $this->language->get('text_otp_modal_text');
        }

        $params = [
            'otp_modal_title'              => $modalTitle,
            'otp_modal_text'               => $modalText,
            'entry_phone'                  => (string) $this->language->get('entry_phone'),
            'button_otp_resend'            => (string) $this->language->get('button_otp_resend'),
            'button_otp_verify'            => (string) $this->language->get('button_otp_verify'),
            'error_otp_invalid_phone'      => (string) $this->language->get('error_otp_invalid_phone'),
            'error_otp_invalid_code'       => (string) $this->language->get('error_otp_invalid_code'),
            'error_otp_expired'            => (string) $this->language->get('error_otp_expired'),
            'error_otp_throttle'           => (string) $this->language->get('error_otp_throttle'),
            'error_otp_attempts_exceeded'  => (string) $this->language->get('error_otp_attempts_exceeded'),
            'error_otp_gateway'            => (string) $this->language->get('error_otp_gateway'),
            'error_otp_required'           => (string) $this->language->get('error_otp_required'),
            'text_otp_code_sent'           => (string) $this->language->get('text_otp_code_sent'),
            'otp_request_url'              => $this->url->link('extension/module/' . $this->moduleName . '/otpRequest', '', true),
            'otp_verify_url'               => $this->url->link('extension/module/' . $this->moduleName . '/otpVerify', '', true),
            'otp_status_url'               => $this->url->link('extension/module/' . $this->moduleName . '/otpStatus', '', true),
            'otp_protect_register'         => $protectRegister,
            'otp_protect_checkout_std'     => $protectStd,
            'otp_protect_checkout_easy'    => $protectEasy,
            'otp_resend_throttle'          => (int) $this->moduleConfig->get('otp_resend_throttle', 30),
            'otp_code_ttl'                 => (int) $this->moduleConfig->get('otp_code_ttl', 300),
        ];

        try {
            $modalHtml = (string) $this->awCore->render('extension/' . $this->moduleName . '/otp_modal', $params);
        } catch (\Throwable $e) {
            return;
        }

        $cssTag = '<link href="catalog/view/theme/default/stylesheet/' . $this->moduleName . '/otp.css" rel="stylesheet" type="text/css"/>';
        $jsTag = '<script src="catalog/view/javascript/' . $this->moduleName . '/otp.js" type="text/javascript"></script>';

        $injection = "\n" . $cssTag . "\n" . $modalHtml . "\n" . $jsTag . "\n";

        $output = str_replace('</body>', $injection . '</body>', $output);
    }

    /**
     * Detect whether the current request is an AJAX/XHR request.
     *
     * @return bool
     */
    private function isAjaxRequest(): bool
    {
        $header = (string) ($this->request->server['HTTP_X_REQUESTED_WITH'] ?? '');

        return strtolower($header) === 'xmlhttprequest';
    }

    /**
     * Generate a zero-padded 6-digit OTP code.
     *
     * @return string
     */
    private function generateOtpCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Normalize a phone number: keep leading + (if present) and digits only.
     *
     * @param  string $raw
     * @return string
     */
    private function normalizePhone(string $raw): string
    {
        $raw = trim($raw);

        if ($raw === '') {
            return '';
        }

        $hasPlus = $raw[0] === '+';
        $digits = preg_replace('/\D/', '', $raw);

        return ($hasPlus ? '+' : '') . $digits;
    }

    /**
     * Basic phone validation: at least 7 digits.
     *
     * @param  string $phone
     * @return bool
     */
    private function isValidPhone(string $phone): bool
    {
        $digits = preg_replace('/\D/', '', $phone);

        return strlen((string) $digits) >= 7;
    }

    /**
     * @return array
     */
    private function getOtpSession(): array
    {
        return $this->session->data['aw_otp'] ?? [];
    }

    /**
     * @param  array $data
     * @return void
     */
    private function setOtpSession(array $data): void
    {
        $this->session->data['aw_otp'] = $data;
    }

    /**
     * @return void
     */
    private function clearOtpSession(): void
    {
        unset($this->session->data['aw_otp']);
    }

    /**
     * Whether the OTP feature is globally enabled.
     *
     * @return bool
     */
    private function isOtpEnabled(): bool
    {
        return (bool) $this->moduleConfig->get('otp_enabled', false);
    }

    /**
     * Verify whether a (phone, token) pair matches a valid OTP session.
     *
     * @param  string $phone raw phone from form
     * @param  string $token aw_otp_token from form
     * @return bool
     */
    private function isOtpValid(string $phone, string $token): bool
    {
        $session = $this->getOtpSession();

        $verifiedPhone = (string) ($session['verified_phone'] ?? '');
        $verifiedToken = (string) ($session['verified_token'] ?? '');

        if ($verifiedPhone === '' || $verifiedToken === '') {
            return false;
        }

        $normalized = $this->normalizePhone($phone);

        if ($verifiedPhone !== $normalized) {
            return false;
        }

        if ($token !== '' && hash_equals($verifiedToken, $token)) {
            return true;
        }

        // Session-only fallback: standard OC checkout serializes fields from a
        // specific panel, so the hidden aw_otp_token may not reach POST data.
        // Accept if the phone matches a verified session within the TTL window.
        $verifiedAt = (int) ($session['verified_at'] ?? 0);
        $ttl = (int) $this->moduleConfig->get('otp_code_ttl', 300);

        return $verifiedAt > 0 && (time() - $verifiedAt) < $ttl;
    }

    /**
     * Render the OTP SMS body for the current language.
     *
     * @param  string $code
     * @param  string $phone
     * @return string
     */
    private function renderOtpSms(string $code, string $phone): string
    {
        $templates = (array) $this->moduleConfig->get('otp_template', []);
        $languageId = (int) $this->config->get('config_language_id');
        $template = (string) ($templates[$languageId] ?? '');

        if ($template === '') {
            $this->load->language('extension/module/' . $this->moduleName);
            $template = (string) $this->language->get('text_otp_template_default');
        }

        if ($template === '' || $template === 'text_otp_template_default') {
            $template = 'Your confirmation code: {{code}}';
        }

        try {
            $message = $this->awCore->render($template, [
                'code'  => $code,
                'phone' => $phone,
            ], true);
        } catch (\Throwable $e) {
            return '';
        }

        return trim((string) $message);
    }

    /**
     * Dispatch the OTP SMS via the configured gateway.
     *
     * @param  string $phone
     * @param  string $message
     * @return bool   true on dispatch attempt, false if gateway not configured
     */
    private function dispatchOtpSms(string $phone, string $message): bool
    {
        $gateway = (string) $this->moduleConfig->get('sms_notify_gatename', '');
        $username = (string) $this->moduleConfig->get('sms_notify_gate_username', '');

        if ($gateway === '' || $username === '') {
            return false;
        }

        $options = [
            'to'       => $phone,
            'from'     => (string) $this->moduleConfig->get('sms_notify_from', ''),
            'username' => $username,
            'password' => (string) $this->moduleConfig->get('sms_notify_gate_password', ''),
            'message'  => $message,
            'viber'    => [
                'status' => false,
            ],
        ];

        try {
            $dispatcher = new SmsDispatcher(
                $gateway,
                $options,
                (string) $this->moduleConfig->get('sms_notify_log_filename', $this->moduleName)
            );
            $dispatcher->send();
        } catch (\Throwable $e) {
            return false;
        }

        return true;
    }

    /**
     * Build the JSON-encoded body that overrides the controller output when an OTP gate fails.
     * Also writes the JSON directly to the response so non-AJAX POSTs still return the message.
     *
     * @return string
     */
    private function buildGateBlockResponse(): string
    {
        $this->load->language('extension/module/' . $this->moduleName);

        $body = json_encode([
            'error'           => $this->language->get('error_otp_required'),
            'aw_otp_required' => true,
        ]);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput($body);

        return $body;
    }

    /**
     * Send a JSON response and end the action.
     *
     * @param  array $payload
     * @return void
     */
    private function respondJson(array $payload): void
    {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($payload));
    }
}

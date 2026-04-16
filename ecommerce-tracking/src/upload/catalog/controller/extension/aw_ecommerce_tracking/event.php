<?php

/**
 * AW E-commerce Tracking (GA4) - Event Handler
 *
 * Injects GA4 tracking code into page output via OpenCart event system.
 * Template-independent, no OCMOD needed. Works with any theme on OC 2.3–3.x.
 *
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionAwEcommerceTrackingEvent extends Controller
{
    private string $moduleName = 'aw_ecommerce_tracking';

    private string $controllerRoute = 'extension/module/aw_ecommerce_tracking';

    /**
     * Inject GTM/gtag head code + JS config + JS file into <head>.
     */
    public function viewHeaderAfter(&$route, &$data, &$output): void
    {
        $html = $this->load->controller($this->controllerRoute . '/getHeader');

        if ($html) {
            $output = str_replace('</head>', $html . "\n</head>", $output);
        }
    }

    /**
     * Inject GTM body code before </body>.
     * Also fires deferred login/signup events (catches redirects to any page).
     */
    public function viewFooterAfter(&$route, &$data, &$output): void
    {
        $html = $this->load->controller($this->controllerRoute . '/getBody');

        $loginHtml = $this->load->controller($this->controllerRoute . '/accountLogin');
        $signupHtml = $this->load->controller($this->controllerRoute . '/accountSuccess');

        $combined = $html . $loginHtml . $signupHtml;

        if ($combined) {
            $this->inject($combined, $output);
        }
    }

    /**
     * Category page — view_item_list event.
     */
    public function viewCategoryAfter(&$route, &$data, &$output): void
    {
        $products = $this->loadProducts($data);
        if (empty($products)) {
            return;
        }

        $categoryId = 0;
        if (!empty($this->request->get['path'])) {
            $parts = explode('_', $this->request->get['path']);
            $categoryId = (int) end($parts);
        }

        $args = [
            'products' => $products,
            'category_info' => ['name' => $data['heading_title'] ?? ''],
            'category_id' => $categoryId,
        ];

        $html = $this->load->controller($this->controllerRoute . '/category', $args);
        $this->inject($html, $output);
    }

    /**
     * Search results — view_item_list + search event.
     */
    public function viewSearchAfter(&$route, &$data, &$output): void
    {
        $products = $this->loadProducts($data);

        $html = '';

        if (!empty($products)) {
            $args = ['products' => $products];
            $html .= $this->load->controller($this->controllerRoute . '/search', $args);
        }

        $searchTerm = $this->request->get['search'] ?? '';
        if ($searchTerm) {
            $html .= $this->renderSearchEvent($searchTerm);
        }

        $this->inject($html, $output);
    }

    /**
     * Manufacturer page — view_item_list event.
     */
    public function viewManufacturerAfter(&$route, &$data, &$output): void
    {
        $products = $this->loadProducts($data);
        if (empty($products)) {
            return;
        }

        $args = [
            'products' => $products,
            'manufacturer_info' => ['name' => $data['heading_title'] ?? ''],
            'manufacturer_id' => (int) ($this->request->get['manufacturer_id'] ?? 0),
        ];

        $html = $this->load->controller($this->controllerRoute . '/manufacturer', $args);
        $this->inject($html, $output);
    }

    /**
     * Special offers page — view_item_list event.
     */
    public function viewSpecialAfter(&$route, &$data, &$output): void
    {
        $products = $this->loadProducts($data);
        if (empty($products)) {
            return;
        }

        $args = ['products' => $products];
        $html = $this->load->controller($this->controllerRoute . '/special', $args);
        $this->inject($html, $output);
    }

    /**
     * Product page — view_item event.
     */
    public function viewProductAfter(&$route, &$data, &$output): void
    {
        $productId = (int) ($data['product_id'] ?? 0);
        if (!$productId) {
            return;
        }

        $this->load->model('extension/module/' . $this->moduleName);
        $productInfo = $this->{'model_extension_module_' . $this->moduleName}->getProductById($productId);

        if (empty($productInfo)) {
            return;
        }

        $args = ['product_info' => $productInfo];
        $html = $this->load->controller($this->controllerRoute . '/product', $args);
        $this->inject($html, $output);
    }

    /**
     * Cart page — view_cart event.
     */
    public function viewCartAfter(&$route, &$data, &$output): void
    {
        $html = $this->load->controller($this->controllerRoute . '/cart');
        $this->inject($html, $output);
    }

    /**
     * Checkout page — begin_checkout event.
     */
    public function viewCheckoutAfter(&$route, &$data, &$output): void
    {
        $html = $this->load->controller($this->controllerRoute . '/checkout');
        $this->inject($html, $output);
    }

    /**
     * AW Easy Checkout main — inject view_cart + begin_checkout events.
     *
     * Easy Checkout uses $this->response->setOutput() instead of returning HTML
     * from the controller, so $output in this handler is null. We read the actual
     * output from $this->response->getOutput() and write it back via setOutput().
     */
    public function controllerEasyCheckoutAfter(&$route, &$data, &$output): void
    {
        $currentOutput = $this->response->getOutput();

        if (!is_string($currentOutput) || empty($currentOutput)) {
            return;
        }

        $cartHtml = $this->load->controller($this->controllerRoute . '/cart');
        $checkoutHtml = $this->load->controller($this->controllerRoute . '/checkout');

        $combined = $cartHtml . $checkoutHtml;

        if (!$combined) {
            return;
        }

        if (strpos($currentOutput, '</body>') !== false) {
            $currentOutput = str_replace('</body>', $combined . "\n</body>", $currentOutput);
        } else {
            $currentOutput .= $combined;
        }

        $this->response->setOutput($currentOutput);
    }

    /**
     * Success page — multiplexer for purchase and signup events.
     *
     * Both checkout/success and account/success render common/success view.
     * We distinguish by checking the current route.
     */
    public function viewSuccessAfter(&$route, &$data, &$output): void
    {
        $currentRoute = $this->request->get['route'] ?? '';

        if (strpos($currentRoute, 'checkout/success') !== false) {
            $orderId = (int) ($this->session->data['last_order_id'] ?? 0);

            if (!$orderId) {
                return;
            }

            $flagKey = 'awTrackPurchaseFired_' . $orderId;
            if (!empty($this->session->data[$flagKey])) {
                return;
            }

            $args = ['order_id' => $orderId];
            $html = $this->load->controller($this->controllerRoute . '/success', $args);

            if ($html) {
                $this->session->data[$flagKey] = true;
                $this->inject($html, $output);
            }
        }
    }

    /**
     * Controller event: set login session flag after successful login.
     */
    public function controllerLoginAfter(&$route, &$data, &$output): void
    {
        if ($this->customer->isLogged()) {
            $this->load->controller($this->controllerRoute . '/setLoginFlag');
        }
    }

    /**
     * Controller event: set signup session flag after successful registration.
     */
    public function controllerRegisterAfter(&$route, &$data, &$output): void
    {
        if ($this->customer->isLogged()) {
            $this->load->controller($this->controllerRoute . '/setSignupFlag');
        }
    }

    /**
     * Featured products module — view_item_list event.
     */
    public function viewModuleFeaturedAfter(&$route, &$data, &$output): void
    {
        $this->handleModuleEvent($data, $output, 'moduleFeatured');
    }

    /**
     * Latest products module — view_item_list event.
     */
    public function viewModuleLatestAfter(&$route, &$data, &$output): void
    {
        $this->handleModuleEvent($data, $output, 'moduleLatest');
    }

    /**
     * Bestseller products module — view_item_list event.
     */
    public function viewModuleBestsellerAfter(&$route, &$data, &$output): void
    {
        $this->handleModuleEvent($data, $output, 'moduleBestseller');
    }

    /**
     * Special products module — view_item_list event.
     */
    public function viewModuleSpecialAfter(&$route, &$data, &$output): void
    {
        $this->handleModuleEvent($data, $output, 'moduleSpecial');
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Extract product IDs from template $data and re-query raw product data.
     */
    private function loadProducts(array $data): array
    {
        if (empty($data['products'])) {
            return [];
        }

        $this->load->model('extension/module/' . $this->moduleName);
        $model = $this->{'model_extension_module_' . $this->moduleName};

        $products = [];

        foreach ($data['products'] as $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            if (!$productId) {
                continue;
            }

            $product = $model->getProductById($productId);
            if ($product) {
                $products[] = $product;
            }
        }

        return $products;
    }

    /**
     * Inject HTML before </body> closing tag.
     */
    private function inject(string $html, string &$output): void
    {
        if ($html) {
            $output = str_replace('</body>', $html . "\n</body>", $output);
        }
    }

    /**
     * Shared handler for product module events (featured, latest, bestseller, special).
     * Module views render as HTML fragments without </body>, so we append directly.
     */
    private function handleModuleEvent(array $data, string &$output, string $method): void
    {
        $products = $this->loadProducts($data);
        if (empty($products)) {
            return;
        }

        $args = ['products' => $products];
        $html = $this->load->controller($this->controllerRoute . '/' . $method, $args);

        if ($html) {
            $output .= $html;
        }
    }

    /**
     * Render GA4 search event with search_term parameter.
     */
    private function renderSearchEvent(string $searchTerm): string
    {
        $searchTerm = htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8');
        $eventData = json_encode([
            'event' => 'search',
            'search_term' => $searchTerm,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return "\n<script>\nwindow.dataLayer = window.dataLayer || [];\ndataLayer.push(" . $eventData . ");\n</script>\n";
    }
}

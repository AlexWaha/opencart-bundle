<?php

/**
 * AW Microdata - Event Handler
 *
 * Injects Schema.org JSON-LD structured data into page output
 * via OpenCart event system (template-independent, no OCMOD needed).
 *
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionAwMicrodataEvent extends Controller
{
    public function viewHeaderAfter(&$route, &$data, &$output): void
    {
        $microdata = $this->load->controller('extension/aw_microdata/microdata/getOg', $data);

        if ($microdata) {
            $output = str_replace('</head>', $microdata . "\n</head>", $output);
        }
    }

    public function viewFooterAfter(&$route, &$data, &$output): void
    {
        $microdata = $this->load->controller('extension/aw_microdata/microdata/getOrganization');

        if ($microdata) {
            $output = str_replace('</body>', $microdata . "\n</body>", $output);
        }
    }

    public function viewHomeAfter(&$route, &$data, &$output): void
    {
        $microdata = $this->load->controller('extension/aw_microdata/microdata/getWebsite')
            . $this->load->controller('extension/aw_microdata/microdata/getHomepageProducts');

        if ($microdata) {
            $output = str_replace('</body>', $microdata . "\n</body>", $output);
        }
    }

    public function viewProductAfter(&$route, &$data, &$output): void
    {
        $microdata = $this->load->controller('extension/aw_microdata/microdata/getProduct', $data)
            . $this->load->controller('extension/aw_microdata/microdata/getBreadcrumbs', $data);

        if ($microdata) {
            $output = str_replace('</body>', $microdata . "\n</body>", $output);
        }
    }

    public function viewCategoryAfter(&$route, &$data, &$output): void
    {
        $microdata = $this->load->controller('extension/aw_microdata/microdata/getCategory', $data)
            . $this->load->controller('extension/aw_microdata/microdata/getBreadcrumbs', $data);

        if ($microdata) {
            $output = str_replace('</body>', $microdata . "\n</body>", $output);
        }
    }

    public function viewInformationAfter(&$route, &$data, &$output): void
    {
        $microdata = $this->load->controller('extension/aw_microdata/microdata/getInformation', $data)
            . $this->load->controller('extension/aw_microdata/microdata/getBreadcrumbs', $data);

        if ($microdata) {
            $output = str_replace('</body>', $microdata . "\n</body>", $output);
        }
    }

    public function viewContactAfter(&$route, &$data, &$output): void
    {
        $microdata = $this->load->controller('extension/aw_microdata/microdata/getContactPage')
            . $this->load->controller('extension/aw_microdata/microdata/getBreadcrumbs', $data);

        if ($microdata) {
            $output = str_replace('</body>', $microdata . "\n</body>", $output);
        }
    }

    public function viewSearchAfter(&$route, &$data, &$output): void
    {
        $microdata = $this->load->controller('extension/aw_microdata/microdata/getSearchResults', $data)
            . $this->load->controller('extension/aw_microdata/microdata/getBreadcrumbs', $data);

        if ($microdata) {
            $output = str_replace('</body>', $microdata . "\n</body>", $output);
        }
    }

    public function viewManufacturerAfter(&$route, &$data, &$output): void
    {
        $microdata = $this->load->controller('extension/aw_microdata/microdata/getManufacturer', $data)
            . $this->load->controller('extension/aw_microdata/microdata/getBreadcrumbs', $data);

        if ($microdata) {
            $output = str_replace('</body>', $microdata . "\n</body>", $output);
        }
    }

    public function viewSpecialAfter(&$route, &$data, &$output): void
    {
        $microdata = $this->load->controller('extension/aw_microdata/microdata/getSpecial', $data)
            . $this->load->controller('extension/aw_microdata/microdata/getBreadcrumbs', $data);

        if ($microdata) {
            $output = str_replace('</body>', $microdata . "\n</body>", $output);
        }
    }

    public function viewBlogArticleAfter(&$route, &$data, &$output): void
    {
        $microdata = $this->load->controller('extension/aw_microdata/microdata/getBlogArticle', $data)
            . $this->load->controller('extension/aw_microdata/microdata/getBreadcrumbs', $data);

        if ($microdata) {
            $output = str_replace('</body>', $microdata . "\n</body>", $output);
        }
    }

    public function viewBlogCategoryAfter(&$route, &$data, &$output): void
    {
        $microdata = $this->load->controller('extension/aw_microdata/microdata/getBlogCategory');

        if ($microdata) {
            $output = str_replace('</body>', $microdata . "\n</body>", $output);
        }
    }
}

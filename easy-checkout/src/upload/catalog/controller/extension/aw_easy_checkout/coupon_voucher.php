<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionAwEasyCheckoutCouponVoucher extends Controller
{
    private string $moduleName = 'aw_easy_checkout';

    public function removeCoupon()
    {
        $json = [];

        unset($this->session->data['coupon']);

        $json['success'] = true;

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function removeVoucher()
    {
        $json = [];

        unset($this->session->data['voucher']);

        $json['success'] = true;

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function getCoupon($data = [])
    {
        $this->load->language('extension/' . $this->moduleName . '/lang');

        $data['coupon'] = $this->request->post['coupon'] ?? $this->session->data['coupon'] ?? '';

        return $this->awCore->render('extension/' . $this->moduleName . '/coupon', $data);
    }

    public function getVoucher($data = [])
    {
        $this->load->language('extension/' . $this->moduleName . '/lang');

        $data['voucher'] = $this->request->post['voucher'] ?? $this->session->data['voucher'] ?? '';

        return $this->awCore->render('extension/' . $this->moduleName . '/voucher', $data);
    }
}

<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ControllerExtensionAwEasyCheckoutCountry extends Controller
{
    private string $moduleName = 'aw_easy_checkout';

    public function index()
    {
        $json = [];

        $this->load->model('localisation/country');

        $countryInfo = $this->model_localisation_country->getCountry($this->request->get['country_id']);

        $addressType = $this->request->get['address_type'] ?? 'shipping';

        if ($addressType === 'payment') {
            $activeZoneId = $this->session->data['payment_address']['zone_id'] ?? 0;
        } else {
            $activeZoneId = $this->session->data['shipping_address']['zone_id'] ?? 0;
        }

        if ($countryInfo) {
            $this->load->model('localisation/zone');

            $json = [
                'active_zone_id' => $activeZoneId,
                'country_id' => $countryInfo['country_id'],
                'name' => $countryInfo['name'],
                'iso_code_2' => $countryInfo['iso_code_2'],
                'iso_code_3' => $countryInfo['iso_code_3'],
                'address_format' => $countryInfo['address_format'],
                'postcode_required' => $countryInfo['postcode_required'],
                'zone' => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
                'status' => $countryInfo['status'],
            ];
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

}

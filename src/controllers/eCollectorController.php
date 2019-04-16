<?php

namespace Leochenftw\eCommerce\eCollector\Controller;
use SilverStripe\CMS\Controllers\ContentController;
use Leochenftw\eCommerce\eCollector\Model\Order;
use SilverStripe\Core\Config\Config;
use Leochenftw\Util;
use Leochenftw\Debugger;

class eCollectorController extends ContentController
{
    protected function route($result)
    {
        $state      =   $result['state'];
        $orderID    =   $result['order_id'];
        $url        =   $this->get_returning_url($state);
        $queries    =   [
                            'order_id'  =>  $orderID,
                            'state'     =>  strtolower($state)
                        ];

        $url        .=  ('?' . http_build_query($queries));

        return $this->redirect($url);
    }

    protected function route_data($state, $order_id)
    {
        return  [
                    'state'         =>  $state,
                    'order_id'      =>  $order_id
                ];
    }

    protected function handle_postback($data)
    {
        user_error("Please implement handle_postback() on " . __CLASS__, E_USER_ERROR);
    }

    public function getOrder($merchant_reference)
    {
        return Order::get()->filter(['MerchantReference' => $merchant_reference])->first();
    }

    protected function get_returning_url($status)
    {
        if ($status == 'Success') {
            return Config::inst()->get('Leochenftw\eCommerce\eCollector', 'MerchantSettings')['SuccessURL'];
        } elseif ($status == 'Cancelled') {
            return Config::inst()->get('Leochenftw\eCommerce\eCollector', 'MerchantSettings')['CancellationURL'];
        } elseif ($status == 'CardSavedOnly') {
            return Config::inst()->get('Leochenftw\eCommerce\eCollector', 'MerchantSettings')['CardSavedURL'];
        }

        return Config::inst()->get('Leochenftw\eCommerce\eCollector', 'MerchantSettings')['FailureURL'];
    }
}

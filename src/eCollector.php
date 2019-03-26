<?php

namespace Leochenftw\eCommerce;
use SilverStripe\Security\Security;
use Leochenftw\Debugger;
use Leochenftw\eCommerce\eCollector\Model\Customer;
use Leochenftw\eCommerce\eCollector\Model\Order;
use Leochenftw\eCommerce\eCollector\Model\OrderItem;
use SilverStripe\Core\Config\Config;

class eCollector
{
    public static function get_cart($order_id = null)
    {
        if (!empty($order_id)) {
            return Order::get()->byID($order_id);
        }

        $member         =   Security::getCurrentUser();

        if (!static::can_order($member)) {
            return null;
        }

        $excluding      =   Config::inst()->get(__CLASS__, 'CartlessProducts');

        if (!empty($member)) {
            if (!$member->isDefaultAdmin()) {
                $order  =   $member->Orders()->exclude(['ClassName' => $excluding])->filter(['Status' => 'Pending'])->first();
            } else {
                $order  =   Order::get()->exclude(['ClassName' => $excluding])->filter(['AnonymousCustomer' => 'Admin_' . session_id(), 'Status' => 'Pending'])->first();
            }
        } else {
            $order      =   Order::get()->exclude(['ClassName' => $excluding])->filter(['AnonymousCustomer' => session_id(), 'Status' => 'Pending'])->first();
        }

        if (empty($order)) {
            $order      =   Order::create();
        }

        $order->write();

        return $order;
    }

    public static function can_order(&$member)
    {
        if (empty($member)) {
            return true;
        }

        return $member->ClassName == Customer::class || $member->isDefaultAdmin();
    }
}

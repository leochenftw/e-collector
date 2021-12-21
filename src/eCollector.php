<?php

namespace Leochenftw\eCommerce;
use SilverStripe\Dev\Debug;
use SilverStripe\Security\Security;
use Leochenftw\Debugger;
use Leochenftw\eCommerce\eCollector\Model\Customer;
use Leochenftw\eCommerce\eCollector\Model\Order;
use Leochenftw\eCommerce\eCollector\Model\OrderItem;
use SilverStripe\Core\Config\Config;
use SilverStripe\Control\Cookie;

class eCollector
{
    public static function get_cart($order_id = null)
    {
        if (!empty($order_id)) {
            return Order::get()->byID($order_id);
        }

        $member =   Security::getCurrentUser();

        if (!static::can_order($member)) {
            return null;
        }

        $excluding      =   Config::inst()->get(__CLASS__, 'CartlessProducts');
        $cookie         =   Cookie::get('eCollectorCookie');
        if (empty($cookie)) {
            $cookie =   session_id();
            Cookie::set('eCollectorCookie', $cookie, $expiry = 30);
        }
        if (!empty($member)) {
            if (!$member->inGroup('administrators')) {
                $order  =   $member->Orders()->exclude(['ClassName' => $excluding])->filter(['Status' => 'Pending'])->first();
            } else {
                $order  =   Order::get()->exclude(['ClassName' => $excluding])->filter(['AnonymousCustomer' => 'Admin_' . $cookie, 'Status' => 'Pending'])->first();
            }
        } else {
            $order      =   Order::get()->exclude(['ClassName' => $excluding])->filter(['AnonymousCustomer' => $cookie, 'Status' => 'Pending'])->first();
        }

        if (empty($order)) {
            $order      =   Order::create();
        } else {
            foreach ($order->Items() as $item) {
                if ($item->hasMethod('syncProduct')) {
                    $item->syncProduct(true);
                }
            }
        }

        if (!$order->exists()) {
            $order->write();
        }

        return $order;
    }

    public static function can_order(&$member)
    {
        if (empty($member)) {
            return true;
        }

        return $member->ClassName == Customer::class || $member->inGroup('administrators');
    }
}

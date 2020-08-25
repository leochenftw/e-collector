<?php

namespace Leochenftw\eCommerce\eCollector\API;
use Leochenftw\eCommerce\eCollector\Model\Order;
use Leochenftw\eCommerce\eCollector\Payment\Payment;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use Leochenftw\Debugger;

class DirectDebit
{
    public static function process(&$order)
    {
        $payment                =   Payment::create();
        $payment->PaymentMethod =   'Direct Debit';
        $payment->Amount        =   $order->getPayTotal();
        $payment->OrderID       =   $order->ID;
        $payment->Status        =   'Debit Pending';
        $payment->IP            =   $_SERVER['REMOTE_ADDR'];

        $payment->write();

        $order->onPaymentUpdate($payment->Status);

        return $order;
    }
}

<?php

namespace Leochenftw\eCommerce\eCollector\Controller;
use Leochenftw\eCommerce\eCollector\API\DPS;
use Leochenftw\eCommerce\eCollector\Payment\Payment;
use Leochenftw\eCommerce\eCollector\Controller\eCollectorController;
use SilverStripe\Core\Injector\Injector;
use Psr\Log\LoggerInterface;
use Leochenftw\Debugger;
use SilverStripe\Core\Config\Config;

class DPSController extends eCollectorController
{
    public function index($request)
    {
        if (!$request->isPost()) {
            Injector::inst()->get(LoggerInterface::class)->info('DPS:: get back');
            if ($token = $request->getVar('result')) {
                $result = $this->handle_postback($token);
                return $this->route($result);
            }
        }

        Injector::inst()->get(LoggerInterface::class)->info('DPS:: post back');

        $token = $request->postVar('result');

        if (empty($token)) {
            $token = $request->getVar('result');
        }

        if (empty($token)) {
            return $this->httpError(400, 'Token is missing');
        }

        $this->handle_postback($token);
    }

    protected function handle_postback($data)
    {
        $result =   DPS::fetch($data);

        if ($Order = $this->getOrder($result['MerchantReference'])) {
            $payment = $Order->Payments()->filter(['TransacID' => $result['TxnId']])->first();

            if (empty($payment)) {
                $payment                =   new Payment();
                $payment->PaymentMethod =   'DPS';
                $payment->CardType      =   $result['CardHolderName'] == 'User Cancelled' ? 'N/A' : $result['CardName'];
                $payment->CardNumber    =   $result['CardHolderName'] == 'User Cancelled' ? 'N/A' : $result['CardNumber'];
                $payment->CardHolder    =   $result['CardHolderName'] == 'User Cancelled' ? 'N/A' : $result['CardHolderName'];
                $payment->Expiry        =   $result['CardHolderName'] == 'User Cancelled' ? 'N/A' : $result['DateExpiry'];
                $payment->TransacID     =   $result['TxnId'];
                $payment->Amount        =   $result['Success'] == '1' ? $result['AmountSettlement'] : 0;
                $payment->OrderID       =   $Order->ID;
                $payment->Status        =   $this->translate_status($result);
                $payment->Message       =   $result['ResponseText'];
                $payment->IP            =   $result['ClientInfo'];

                $payment->write();

                $Order->onPaymentUpdate($payment->Status);
            }

            return $this->route_data($payment->Status, $Order->ID);
        }

        return $this->httpError(400, 'Order not found');
    }

    private function translate_status(&$result)
    {
        if ($result['Success'] == '1') {
            return 'Success';
        } else if ($result['CardHolderName'] == 'User Cancelled') {
            return 'Cancelled';
        }

        return 'Failed';
    }
}

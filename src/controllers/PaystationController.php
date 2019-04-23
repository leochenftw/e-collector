<?php

namespace Leochenftw\eCommerce\eCollector\Controller;
use Leochenftw\eCommerce\eCollector\API\Paystation;
use Leochenftw\eCommerce\eCollector\Payment\Payment;
use Leochenftw\eCommerce\eCollector\Controller\eCollectorController;
use SilverStripe\Core\Injector\Injector;
use Psr\Log\LoggerInterface;
use Leochenftw\Debugger;
use SilverStripe\Core\Config\Config;

class PaystationController extends eCollectorController
{
    public function index($request)
    {
        if (!$request->isPost()) {
            Injector::inst()->get(LoggerInterface::class)->info('Paystation:: get back');
            if ($token = $request->getVar('ti')) {
                $result = $this->handle_getback($token);
                return $this->route($result);
            }
        }

        // Injector::inst()->get(LoggerInterface::class)->info('Paystation:: post back');
        // it appears PS postback some crap that isn't very intuitive - need more research

        if ($xml =   simplexml_load_string($request->getBody())) {
            $json   =   json_encode($xml);

            Injector::inst()->get(LoggerInterface::class)->info($json);

            $token  =   $xml->ti;

            if (empty($token)) {
                Injector::inst()->get(LoggerInterface::class)->info('ti no found');
                return $this->httpError(400, 'transaction id is missing');
            }

            // Injector::inst()->get(LoggerInterface::class)->info('ti found, proceed');

            $this->handle_postback($xml);
        }

        return $this->httpError(400, 'XML?');
    }

    protected function handle_postback($result)
    {
        if ($Order = $this->getOrder($result->MerchantReference)) {
            $payment = $Order->Payments()->filter(['TransacID' => $result->ti])->first();

            if (empty($payment)) {
                $payment                =   new Payment();
                $payment->PaymentMethod =   'Paystation';

                $payment->CardType      =   $result->ct->__toString();
                $payment->CardNumber    =   $result->CardNo->__toString();
                $payment->CardHolder    =   $result->CardholderName->__toString();
                $payment->Expiry        =   $result->CardExpiry->__toString();
                $payment->TransacID     =   $result->ti->__toString();
                $payment->Amount        =   $result->em->__toString() == 'Transaction successful' ? ($result->PurchaseAmount->__toString() * 0.01) : 0;
                $payment->OrderID       =   $Order->ID;
                $payment->Status        =   $result->em->__toString() == 'Transaction successful' ? 'Success' : 'Failed';
                $payment->Message       =   $result->em->__toString();
                $payment->IP            =   $result->RequestIP->__toString();

                $payment->write();

                $Order->onPaymentUpdate($payment->Status);
            }

            return 'Cool, thanks!';
        }

        return $this->httpError(400, 'Order not found');
    }

    protected function handle_getback($data)
    {
        $result             =   $this->request->postVars();
        if (empty($result)) {
            $result         =   $this->request->getVars();
        }

        if ($Order = $this->getOrder($result['merchant_ref'])) {
            $payment = $Order->Payments()->filter(['TransacID' => $result['ti']])->first();

            if (empty($payment)) {
                $payment                =   new Payment();
                $payment->PaymentMethod =   'Paystation';
                $payment->CardNumber    =   $result['cardno'];
                $payment->Expiry        =   $result['cardexp'];
                $payment->TransacID     =   $result['ti'];
                $payment->Amount        =   $result['em'] == 'Transaction successful' ? ($result['am'] / 100) : 0;
                $payment->OrderID       =   $Order->ID;
                $payment->Status        =   $this->translate_status($result);
                $payment->Message       =   $result['em'];

                $payment->write();

                $Order->onPaymentUpdate($payment->Status);
            }

            return $this->route_data($payment->Status, $Order->ID);
        }

        return $this->httpError(400, 'Order not found');
    }

    private function translate_status(&$result)
    {
        if ($result['em'] == 'Transaction successful') {
            return 'Success';
        }

        return 'Failed';
    }
}

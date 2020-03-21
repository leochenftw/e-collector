<?php
namespace Leochenftw\eCommerce\eCollector\Controller;
use Leochenftw\eCommerce\eCollector\API\POLi;
use Leochenftw\eCommerce\eCollector\Payment\Payment;
use Leochenftw\eCommerce\eCollector\Controller\eCollectorController;
use SilverStripe\Core\Injector\Injector;
use Psr\Log\LoggerInterface;
use Leochenftw\Debugger;
use SilverStripe\Core\Config\Config;

class PoliController extends eCollectorController
{
    public function index($request)
    {
        if (!$request->isPost()) {
            Injector::inst()->get(LoggerInterface::class)->info('Poli:: get back');
            if ($token = $request->getVar('token')) {
                $result = $this->handle_postback($token);
                return $this->route($result);
            }
        }

        Injector::inst()->get(LoggerInterface::class)->info('Poli:: post back');

        $token = $request->postVar('Token');

        if (empty($token)) {
            $token = $request->getVar('token');
        }

        if (empty($token)) {
            return $this->httpError(400, 'Token is missing');
        }

        $this->handle_postback($token);
    }

    protected function handle_postback($data)
    {
        $result             =   POLi::fetch($data);

        // Debugger::inspect($result);

        if ($Order = $this->getOrder($result['MerchantReference'])) {
            $payment = $Order->Payments()->filter(['TransacID' => $result['TransactionRefNo']])->first();

            if (empty($payment)) {
                $payment                =   new Payment();
                $payment->PaymentMethod =   'POLi';
                $payment->CardHolder    =   $result['PayerFirstName'] . ' ' . $result['PayerFamilyName'];

                $payment->PayerAccountNumber    =   $result['PayerAccountNumber'];
                $payment->PayerAccountSortCode  =   $result['PayerAccountSortCode'];

                $payment->PayerBankName =   $result['FinancialInstitutionCountryCode'];
                $payment->TransacID     =   $result['TransactionRefNo'];
                $payment->Amount        =   $result['AmountPaid'];
                $payment->OrderID       =   $Order->ID;
                $payment->Status        =   $this->translate_status($result);
                $payment->IP            =   $result['UserIPAddress'];
                if (!empty($result['ErrorMessage'])) {
                    $payment->Message   =   $result['ErrorMessage'];
                }

                $payment->write();

                $Order->onPaymentUpdate($payment->Status);
            }

            return $this->route_data($payment->Status, $Order->ID);
        }

        return $this->httpError(400, 'Order not found');
    }

    private function translate_status(&$result)
    {
        if ($result['TransactionStatus'] == 'Completed') {
            $result['TransactionStatus']    =   'Success';
        } elseif (
            $result['TransactionStatus'] == 'Initiated' ||
            $result['TransactionStatus'] == 'FinancialInstitution Selected' ||
            $result['TransactionStatus'] == 'EulaAccepted' ||
            $result['TransactionStatus'] == 'Unknown' ||
            $result['TransactionStatus'] == 'ReceiptUnverified' ||
            $result['TransactionStatus'] == 'TimedOut'
        ) {
            $result['TransactionStatus']    =   'Pending';
        }

        return $result['TransactionStatus'];
    }
}

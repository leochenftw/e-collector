<?php

namespace Leochenftw\eCommerce\eCollector\Controller;
use Leochenftw\eCommerce\eCollector\GatewayResponse;
use PageController;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\TabSet;
use SilverStripe\Forms\Tab;
use Leochenftw\eCommerce\eCollector;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\Form;
use Leochenftw\Debugger;
use Leochenftw\eCommerce\eCollector\API\Paystation;
use Leochenftw\eCommerce\eCollector\API\POLi;
use Leochenftw\eCommerce\eCollector\API\DPS;
use Leochenftw\eCommerce\eCollector\Model\Order;
use SilverStripe\View\ArrayData;
use Leochenftw\eCommerce\eCollector\Model\Freight;
use SilverStripe\Forms\LiteralField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Core\Config\Config;
use SilverStripe\Control\HTTPRequest;

class CheckoutController extends PageController
{
    public function index(HTTPRequest $request)
    {
        if ($status =   $this->request->Param('status')) {
            if ($status == 'pay') {
                return $this->doPay();
            } else {
                return $this->customise($this->displayComplete())->renderWith(['CheckoutComplete', 'Page']);
            }
        }

        $this->extend('index');
        return $this->renderWith(['Checkout', 'Page']);
    }

    private function displayComplete()
    {
        return  ArrayData::create([
                    'Title'     =>  $this->TitleMaker()
                ]);
    }

    private function TitleMaker()
    {
        $order      =   Order::get()->byID($this->request->getVar('order_id'));
        return 'Order#' . $order->ID . ' status: ' . $order->Status;
    }

    public function Title()
    {
        return 'Checkout';
    }

    public function Link($action = NULL)
    {
        return '/checkout/';
    }

    public function init()
    {
        parent::init();
    }

    private function CheckoutForm()
    {
        $fields         =   new FieldList();
        $fields->push(LiteralField::create('TotalAmount', '<p>Total amount: $' . eCollector::get_cart()->TotalAmount . '</p>'));
        $fields->push(LiteralField::create('TotalWeight', '<p>Total weight: ' . eCollector::get_cart()->TotalWeight . 'KG</p>'));
        $fields->push(LiteralField::create('TotalItems', '<p>Total items: ' . eCollector::get_cart()->ItemCount() . ' item(s)</p>'));
        $fields->push(OptionsetField::create('Freight', 'Freight', $this->getFreightOptions()));

        $gateways       =   Config::inst()->get('Leochenftw\eCommerce\eCollector', 'GatewaySettings');
        $options        =   [];

        foreach ($gateways as $key => $gateway) {
            $options[]  =   [
                                'ID'    =>  $key,
                                'Title' =>  $key
                            ];
        }

        $fields->push(OptionsetField::create('Gateway', 'Payment method', ArrayList::create($options)));

        $actions        =   new FieldList();

        $actions->push(FormAction::create('doPay', 'Pay'));

        $form           =   new Form($this, 'ScriptPurchaseForm', $fields, $actions);

        $form->setFormMethod('POST')->setFormAction($this->Link() . 'pay');

        return $form;
    }

    public function getFreightOptions()
    {
        $options        =   [];
        $freights       =   $this->getFreights();
        foreach ($freights as $freight)
        {
            $options[]  =   [
                                'ID'    =>  $freight->ID,
                                'Title' =>  $freight->Title . ', ' . $freight->getSummaryPrice()
                            ];
        }

        return ArrayList::create($options);
    }

    public function getFreights()
    {
        $frieghts       =   Freight::get();
        $this->extend('getFreights', $freights);
        return $frieghts;
    }

    public function doPay()
    {
        if (empty($this->request->postVar('Gateway'))) {
            $this->Form->sessionError('Please choose a payment method');
            return $this->redirectBack();
        }

        if (empty($this->request->postVar('Freight'))) {
            $this->Form->sessionError('Please choose a freight option');
            return $this->redirectBack();
        }

        $method     =   'Leochenftw\eCommerce\eCollector\API\\' . $this->request->postVar('Gateway');
        $cart       =   eCollector::get_cart();
        $amount     =   $cart->TotalAmount;

        if ($amount == 0) {
            $this->Form->sessionError('There is nothing to pay!');
            return $this->redirectBack();
        }

        if ($fid = $this->request->postVar('Freight')) {
            $freight    =   Freight::get()->byID($fid);
            $cost       =   $freight->CalculateCost($cart->TotalWeight, $cart->ItemCount());
            $amount     +=  $cost;
        }

        $gateway    =   ($method)::process($amount, $cart->MerchantReference);

        $response   =   new GatewayResponse($method, $gateway);
        $uri        =   $response->uri;
        if (!empty($uri)) {
            return $this->redirect($uri);
        }

        return $this->httpError(503, 'Payment gateway failed. ' . "\nError message: " . $response->error);
    }
}

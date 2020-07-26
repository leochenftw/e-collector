<?php

namespace Leochenftw\eCommerce\eCollector\Controller;
use PageController;
use SilverStripe\SiteConfig\SiteConfig;
use Leochenftw\eCommerce\eCollector;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\Form;
use Leochenftw\Debugger;
use Leochenftw\eCommerce\eCollector\API\Paystation;
use Leochenftw\eCommerce\eCollector\Model\Order;
use SilverStripe\View\ArrayData;
use SilverStripe\Control\HTTPRequest;
use Page;
/**
 * Description
 *
 * @package silverstripe
 * @subpackage mysite
 */
class CartController extends PageController
{
    public function index(HTTPRequest $request)
    {
        if ($this->request->isPost() && !empty($this->request->postVar('action'))) {
            $this->{'do_' . $this->request->postVar('action')}();
            $this->redirect($this->Link());
        }

        return parent::index($request);
    }

    public function getData()
    {
        return Page::create()->getData();
    }

    private function do_update()
    {
        $item               =   $this->getCart()->Items()->byID($this->request->postVar('id'));

        if ($this->request->postVar('qty') == 0) {
            $item->delete();
        } elseif ($item->Quantity != $this->request->postVar('qty')) {
            $item->Quantity =   $this->request->postVar('qty');
            $item->write();
        }
    }

    private function do_delete()
    {
        $item   =   $this->getCart()->Items()->byID($this->request->postVar('id'));
        $item->delete();
    }

    public function getCart()
    {
        return eCollector::get_cart();
    }

    public function Title()
    {
        $URIs   =   explode('/', ltrim($_SERVER['REQUEST_URI'], '/'));
        if (count($URIs) > 2) {
            $third  =   $URIs[count($URIs) - 1];
            $third  =   explode('?', $third)[0];
            return 'Payment ' . $third . ($third == 'cancel' ? 'led' : '');
        } elseif (count($URIs) > 1) {
            $second =   $URIs[count($URIs) - 1];
            $second =   explode('?', $second)[0];
            return ucwords($second);
        }

        return 'Cart' . ' | ' . SiteConfig::current_site_config()->Title;
    }

    public function Link($action = NULL)
    {
        return '/cart/';
    }

    public function init()
    {
        parent::init();
        $this->Form =   $this->CartForm();
    }

    private function CartForm()
    {
        $fields     =   new FieldList();
        $actions    =   new FieldList();

        $actions->push(FormAction::create('doCheckout', 'Checkout'));

        $form       =   new Form($this, 'ScriptPurchaseForm', $fields, $actions);

        $form->setFormMethod('POST')->setFormAction('/checkout');

        return $form;
    }
}

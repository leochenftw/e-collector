<?php

namespace Leochenftw\eCommerce\eCollector\Controller;
use PageController;
use Leochenftw\eCommerce\eCollector;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\Form;
use Leochenftw\Debugger;
use Leochenftw\eCommerce\eCollector\API\Paystation;
use Leochenftw\eCommerce\eCollector\Model\Order;
use SilverStripe\View\ArrayData;

/**
 * Description
 *
 * @package silverstripe
 * @subpackage mysite
 */
class CartController extends PageController
{
    public function index()
    {
        if ($this->request->isPost() && !empty($this->request->postVar('action'))) {
            $this->{'do_' . $this->request->postVar('action')}();
            $this->redirect($this->Link());
        }

        return $this->renderWith(['Cart', 'Page']);
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
        return 'Cart';
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

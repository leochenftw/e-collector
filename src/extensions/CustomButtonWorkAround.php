<?php

namespace Leochenftw\eCommerce\eCollector\Extensions;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Control\Controller;
use Leochenftw\eCommerce\eCollector\Model\Order;
use SilverStripe\View\Requirements;
use SilverStripe\Core\Config\Config;
use Leochenftw\Debugger;

class CustomButtonWorkAround extends Extension
{
    /**
     * Defines methods that can be called directly
     * @var array
     */
    private static $allowed_actions = [
        'send_invoice'      =>  true,
        'send_tracking'     =>  true,
        'refund'            =>  true,
        'cheque_cleared'    =>  true,
        'debit_cleared'     =>  true
    ];

    public function updateFormActions(FieldList $actions)
    {
        $list   =   [
            Order::class
            // add models below
        ];

        $record =   $this->owner->getRecord();

        if ((!in_array($record->ClassName, $list) && !is_subclass_of($record->ClassName::create(), Order::class)) || !$record->exists()) {
            return;
        }

        $buttons = Config::inst()->get(Order::class, 'default_buttons');

        // Now, custom actions start
        Requirements::javascript('leochenftw/e-collector: client/js/cms.js');

        if ($buttons['send_invoice']) {
            $actions->push(FormAction::create('send_invoice', 'Send invoice')
                ->setUseButtonTag(true)
                ->addExtraClass('btn-outline-primary btn-send-invoice'));
        }

        if ($record->Status == 'Payment Received' || $record->Status == 'Shipped') {
            if ($buttons['refund']) {
                $actions->push(FormAction::create('refund', 'Refund')
                    ->setUseButtonTag(true)
                    ->addExtraClass('btn-danger btn-refund'));
            }
        } elseif ($record->Status == 'Invoice Pending') {
            if ($buttons['cheque_cleared']) {
                $actions->push(FormAction::create('cheque_cleared', 'Cheque Cleared')
                    ->setUseButtonTag(true)
                    ->addExtraClass('btn-primary btn-cheque-cleared'));
            }
        } elseif ($record->Status == 'Debit Pending') {
            if ($buttons['debit_cleared']) {
                $actions->push(FormAction::create('debit_cleared', 'Direct Debit Received')
                    ->setUseButtonTag(true)
                    ->addExtraClass('btn-primary btn-cheque-cleared'));
            }
        }

        if (!empty($record->FreightID) && !empty($record->TrackingNumber)) {
            if ($buttons['send_tracking']) {
                $actions->push(FormAction::create('send_tracking', 'Send Tracking Number')
                    ->setUseButtonTag(true)
                    ->addExtraClass('btn-primary btn-send-tracking'));
            }
        }
        // OK, custom actions finish

        $right_group = $actions->fieldByName('RightGroup');
        $actions->remove($right_group);
        $actions->push($right_group);
    }

    // Make custom actions work
    public function send_invoice($data, $form)
    {
        $this->owner->getRecord()->send_invoice();

        $form->sessionMessage('Invoice has been sent!', 'good', ValidationResult::CAST_HTML);
        return Controller::curr()->redirectBack();
    }

    public function send_tracking($data, $form)
    {
        $this->owner->getRecord()->send_tracking();

        $form->sessionMessage('Tracking information has been sent!', 'good', ValidationResult::CAST_HTML);
        return Controller::curr()->redirectBack();
    }

    public function cheque_cleared($data, $form)
    {
        $this->owner->getRecord()->cheque_cleared();

        $form->sessionMessage('Payment has been set to successful', 'good', ValidationResult::CAST_HTML);
        return Controller::curr()->redirectBack();
    }

    public function refund($data, $form)
    {
        $this->owner->getRecord()->refund();

        $form->sessionMessage('Order has been refunded :(', 'good', ValidationResult::CAST_HTML);
        return Controller::curr()->redirectBack();
    }

    public function debit_cleared($data, $form)
    {
        $this->owner->getRecord()->debit_cleared();

        $form->sessionMessage('Payment has been set to successful', 'good', ValidationResult::CAST_HTML);
        return Controller::curr()->redirectBack();
    }
}

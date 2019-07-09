<?php

namespace Leochenftw\eCommerce\eCollector\Extensions;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Control\Controller;
use Leochenftw\eCommerce\eCollector\Model\Order;
use SilverStripe\View\Requirements;

class CustomButtonWorkAround extends Extension
{
    /**
     * Defines methods that can be called directly
     * @var array
     */
    private static $allowed_actions = [
        'send_invoice'  =>  true,
        'send_tracking' =>  true,
        'refund'        =>  true
    ];

    public function updateFormActions(FieldList $actions)
    {
        $list   =   [
            Order::class
            // add models below
        ];

        $record =   $this->owner->getRecord();

        if (!in_array($record->ClassName, $list) || !$record->exists()) {
            return;
        }

        // Now, custom actions start
        if ($record->ClassName == Order::class) {
            Requirements::javascript('leochenftw/e-collector: client/js/cms.js');
            $actions->push(FormAction::create('send_invoice', 'Send invoice')
                ->setUseButtonTag(true)
                ->addExtraClass('btn-outline-primary btn-send-invoice'));

            if ($record->Status == 'Payment Received' || $record->Status == 'Shipped') {
                $actions->push(FormAction::create('refund', 'Refund')
                    ->setUseButtonTag(true)
                    ->addExtraClass('btn-danger btn-refund'));
            }

            if (!empty($record->FreightID) && !empty($record->TrackingNumber)) {
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

    public function refund($data, $form)
    {
        $this->owner->getRecord()->refund();

        $form->sessionMessage('Order has been refunded :(', 'good', ValidationResult::CAST_HTML);
        return Controller::curr()->redirectBack();
    }
}

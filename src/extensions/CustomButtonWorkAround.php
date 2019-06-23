<?php

namespace Leochenftw\eCommerce\eCollector\Extensions;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Control\Controller;
use Leochenftw\eCommerce\eCollector\Model\Order;

class CustomButtonWorkAround extends Extension
{
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
            $actions->push(FormAction::create('send_invoice', 'Send invoice')
                ->setUseButtonTag(true)
                ->setAttribute('data-icon', 'accept'));

            if (!empty($record->FreightID) && !empty($record->TrackingNumber)) {
                $actions->push(FormAction::create('send_tracking', 'Send Tracking Number')
                    ->setUseButtonTag(true)
                    ->addExtraClass('btn-primary'));
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

        $form->sessionMessage('Invoice has been sent!', 'good', ValidationResult::CAST_HTML);
        return Controller::curr()->redirectBack();
    }
}

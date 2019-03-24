<?php

namespace Leochenftw\eCommerce\eCollector\Extensions;

use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;

class ProductExtension extends DataExtension
{
    /**
     * Database fields
     * @var array
     */
    private static $db = [
        'SKU'           =>  'Varchar(64)',
        'OutOfStock'    =>  'Boolean',
        'Price'         =>  'Currency',
        'UnitWeight'    =>  'Decimal'
    ];

    private static $indexes = [
        'SKU'   =>  true
    ];

    /**
     * Update Fields
     * @return FieldList
     */
    public function updateCMSFields(FieldList $fields)
    {
        $owner = $this->owner;
        if ($owner->isDigital) {
            $fields->removeByName([
                'UnitWeight'
            ]);
        }
        return $fields;
    }

    /**
     * Event handler called before writing to the database.
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if ($this->owner->isDigital) {
            $this->owner->UnitWeight    =   0;
        }
    }
}

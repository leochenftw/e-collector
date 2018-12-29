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
        'isDigital'     =>  'Boolean',
        'SKU'           =>  'Varchar(64)',
        'OutOfStock'    =>  'Boolean',
        'Price'         =>  'Currency',
        'UnitWeight'    =>  'Decimal'
    ];
}

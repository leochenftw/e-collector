<?php

namespace Leochenftw\eCommerce\eCollector\Extensions;

use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;

class ProductOrderItemCommonFields extends DataExtension
{
    /**
     * Database fields
     * @var array
     */
    private static $db = [
        'isDigital'     =>  'Boolean',
        'isExempt'      =>  'Boolean',
        'NoDiscount'    =>  'Boolean'
    ];
}

<?php

namespace Leochenftw\eCommerce\eCollector\Model;
use SilverStripe\ORM\DataObject;
use Leochenftw\eCommerce\eCollector\Model\Order;

/**
 * Description
 *
 * @package silverstripe
 * @subpackage mysite
 */
class OrderItem extends DataObject
{
    /**
     * Defines the database table name
     * @var string
     */
    private static $table_name = 'ECollectorOrderItem';
    /**
     * Database fields
     * @var array
     */
    private static $db = [
        'Quantity'      =>  'Int',
        'Subtotal'      =>  'Currency',
        'Subweight'     =>  'Decimal',
        'isRefunded'    =>  'Boolean'
    ];

    /**
     * Defines a default list of filters for the search context
     * @var array
     */
    private static $searchable_fields = [
        'ID'
    ];

    /**
     * Add default values to database
     * @var array
     */
    private static $defaults = [
        'Quantity'  =>  1
    ];

    /**
     * Has_one relationship
     * @var array
     */
    private static $has_one = [
        'Order'     =>  Order::class
    ];
}

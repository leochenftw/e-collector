<?php

namespace Leochenftw\eCommerce\eCollector\Model;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use Leochenftw\eCommerce\eCollector\Model\OrderItem;
use Leochenftw\eCommerce\eCollector\Model\Customer;
use Leochenftw\eCommerce\eCollector\Payment\Payment;
use Leochenftw\Debugger;
use SilverStripe\Core\Config\Config;
use Leochenftw\eCommerce\eCollector\API\DPS;
use Leochenftw\eCommerce\eCollector\API\Poli;
use Leochenftw\eCommerce\eCollector\API\Paystation;
use Leochenftw\eCommerce\eCollector\Model\Freight;

/**
 * Description
 *
 * @package silverstripe
 * @subpackage mysite
 */
class Order extends DataObject
{
    /**
     * Defines the database table name
     * @var string
     */
    private static $table_name = 'ECollectorOrder';
    /**
     * Database fields
     * @var array
     */
    private static $db = [
        'MerchantReference'     =>  'Varchar(64)',
        'Status'                =>  'Enum("Pending,Payment Received,Shipped,Cancelled,Refunded,Completed")',
        'AnonymousCustomer'     =>  'Varchar(128)',
        'TotalAmount'           =>  'Currency',
        'TotalWeight'           =>  'Decimal'
    ];

    private static $indexes = [
        'MerchantReference'     =>  true,
        'MerchantReference'     =>  [
                                        'type'      =>  'index',
                                        'columns'   =>  ['MerchantReference']
                                    ]
    ];

    private static $cascade_deletes = [
        'Items'
    ];

    /**
     * Defines summary fields commonly used in table columns
     * as a quick overview of the data for this dataobject
     * @var array
     */
    private static $summary_fields = [
        'ID'                    =>  'ID',
        'ItemCount'             =>  'Item(s)',
        'Status'                =>  'Status'
    ];

    /**
     * Defines a default list of filters for the search context
     * @var array
     */
    private static $searchable_fields = [
        'MerchantReference',
        'Status'
    ];

    /**
     * Has_one relationship
     * @var array
     */
    private static $has_one = [
        'Customer'  =>  Customer::class,
        'Freight'   =>  Freight::class
    ];

    /**
     * Default sort ordering
     * @var array
     */
    private static $default_sort = ['ID' => 'DESC'];

    public function populateDefaults()
    {
        $member                         =   Member::currentUser();
        if (!empty($member) && $member->ClassName == Customer::class) {
            $this->CustomerID           =   $member->ID;
        } elseif (empty($member)) {
            $this->AnonymousCustomer   =   session_id();
        } else {
            $this->AnonymousCustomer   =   'Admin_' . session_id();
        }

        $this->MerchantReference        =   sha1(md5(round(microtime(true) * 1000) . '-' . session_id()));
    }

    /**
     * Has_many relationship
     * @var array
     */
    private static $has_many = [
        'Items'     =>  OrderItem::class,
        'Payments'  =>  Payment::class
    ];

    /**
     * CMS Fields
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields =   parent::getCMSFields();

        if ($this->exists()) {
            $items  =   $fields->fieldByName('Root.Items.Items');

            $fields->removeByName([
                'Items'
            ]);

            $fields->addFieldToTab(
                'Root.Main',
                $items
            );
        }

        $fields->fieldByName('Root.Main.FreightID')->setTitle('Freight Provider');

        // Debugger::inspect(Paystation::process($this->getTotalAmount(), $this->MerchantReference));

        return $fields;
    }

    public function ItemCount()
    {
        $items  =   $this->Items();
        $n      =   0;
        foreach ($items as $item) {
            $n  +=  $item->Quantity;
        }
        return $n;
    }

    public function UpdateAmountWeight()
    {
        $amount =   0;
        $weight =   0;

        foreach ($this->Items() as $item) {
            $amount +=  $item->Subtotal;
            $weight +=  $item->Subweight;
        }

        $this->TotalAmount  =   $amount;
        $this->TotalWeight  =   $weight;
        $this->write();
    }

    public function onPaymentUpdate($status)
    {
        if ($status == 'Success') {
            $this->Status   =   'Payment Received';
        } else {
            $this->Status   =   'Pending';
        }

        $this->write();
    }
}

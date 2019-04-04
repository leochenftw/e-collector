<?php

namespace Leochenftw\eCommerce\eCollector\Model;
use Leochenftw\eCommerce\eCollector;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\TextareaField;
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
        'Status'                =>  'Enum("Pending,Invoice Pending,Payment Received,Shipped,Cancelled,Refunded,Completed")',
        'AnonymousCustomer'     =>  'Varchar(128)',
        'TotalAmount'           =>  'Currency',
        'TotalWeight'           =>  'Decimal',
        'PayableTotal'          =>  'Currency',
        'Email'                 =>  'Varchar(256)',
        'Phone'                 =>  'Varchar(128)',
        'ShippingFirstname'     =>  'Varchar(128)',
        'ShippingSurname'       =>  'Varchar(128)',
        'ShippingAddress'       =>  'Text',
        'ShippingOrganisation'  =>  'Varchar(128)',
        'ShippingApartment'     =>  'Varchar(64)',
        'ShippingSuburb'        =>  'Varchar(128)',
        'ShippingTown'          =>  'Varchar(128)',
        'ShippingRegion'        =>  'Varchar(128)',
        'ShippingCountry'       =>  'Varchar(128)',
        'ShippingPostcode'      =>  'Varchar(128)',
        'ShippingPhone'         =>  'Varchar(128)',
        'SameBilling'           =>  'Boolean',
        'BillingFirstname'      =>  'Varchar(128)',
        'BillingSurname'        =>  'Varchar(128)',
        'BillingAddress'        =>  'Text',
        'BillingOrganisation'   =>  'Varchar(128)',
        'BillingApartment'      =>  'Varchar(64)',
        'BillingSuburb'         =>  'Varchar(128)',
        'BillingTown'           =>  'Varchar(128)',
        'BillingRegion'         =>  'Varchar(128)',
        'BillingCountry'        =>  'Varchar(128)',
        'BillingPostcode'       =>  'Varchar(128)',
        'BillingPhone'          =>  'Varchar(128)',
        'Comment'               =>  'Text'
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
        $this->SameBilling  =   true;
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

    public function getSuccessPayment()
    {
        if ($this->exists() && $this->Payments()->exists()) {
            return $this->Payments()->filter(['Status' => 'Success'])->first();
        }

        return null;
    }

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

        $fields->addFieldsToTab(
            'Root.Shipping',
            [
                TextField::create('ShippingFirstname', 'Firstname'),
                TextField::create('ShippingSurname', 'Surname'),
                TextField::create('ShippingOrganisation', 'Organisation'),
                TextField::create('ShippingApartment', 'Apartment'),
                TextareaField::create('ShippingAddress', 'Address'),
                TextField::create('ShippingSuburb', 'Suburb'),
                TextField::create('ShippingTown', 'Town'),
                TextField::create('ShippingRegion', 'Region'),
                TextField::create('ShippingCountry', 'Country'),
                TextField::create('ShippingPostcode', 'Postcode'),
                TextField::create('ShippingPhone', 'Phone'),
            ]
        );

        $fields->addFieldsToTab(
            'Root.Billing',
            [
                TextField::create('BillingFirstname', 'Firstname'),
                TextField::create('BillingSurname', 'Surname'),
                TextField::create('BillingOrganisation', 'Organisation'),
                TextField::create('BillingApartment', 'Apartment'),
                TextareaField::create('BillingAddress', 'Address'),
                TextField::create('BillingSuburb', 'Suburb'),
                TextField::create('BillingTown', 'Town'),
                TextField::create('BillingRegion', 'Region'),
                TextField::create('BillingCountry', 'Country'),
                TextField::create('BillingPostcode', 'Postcode'),
                TextField::create('BillingPhone', 'Phone'),
            ]
        );

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

    public function ShippableItemCount()
    {
        $items  =   $this->Items()->filter(['isDigital' => false]);
        $n      =   0;
        foreach ($items as $item) {
            $n  +=  $item->Quantity;
        }
        return $n;
    }

    public function UpdateAmountWeight()
    {
        $amount     =   0;
        $weight     =   0;
        $payable    =   0;

        foreach ($this->Items() as $item) {
            $amount     +=  $item->Subtotal;
            $weight     +=  $item->Subweight;
            $payable    +=  $item->PayableTotal;
        }

        $this->TotalAmount  =   $amount;
        $this->TotalWeight  =   $weight;
        $this->PayableTotal =   $payable;
        $this->write();
    }

    public function onPaymentUpdate($status)
    {
        if ($status == 'Success') {
            $this->Status   =   'Payment Received';
        } elseif ($status == 'Invoice Pending') {
            $this->Status   =   $status;
        } else {
            $this->Status   =   'Pending';
        }

        $this->write();
    }

    /**
     * Event handler called before writing to the database.
     */
     public function onBeforeWrite()
     {
         parent::onBeforeWrite();
         if ($this->SameBilling) {
             $this->BillingOrganisation =   $this->ShippingOrganisation;
             $this->BillingApartment    =   $this->ShippingApartment;
             $this->BillingAddress      =   $this->ShippingAddress;
             $this->BillingSuburb       =   $this->ShippingSuburb;
             $this->BillingTown         =   $this->ShippingTown;
             $this->BillingRegion       =   $this->ShippingRegion;
             $this->BillingCountry      =   $this->ShippingCountry;
             $this->BillingPostcode     =   $this->ShippingPostcode;
             $this->BillingPhone        =   $this->ShippingPhone;
         }
     }

     public function getGST()
     {
         $gst   =   0;
         $rate  =   (float) Config::inst()->get(eCollector::class, 'GSTRate');
         $items =   $this->Items()->filter(['isExempt' => false]);
         foreach ($items as $item) {
             $gst   +=  $item->PayableTotal * $rate;
         }

         return $gst;
     }

     public function Discount()
     {
         $list  =   [];
         $items =   $this->Items()->filter(['NoDiscount' => false]);
         foreach ($items as $item) {
             if ($item->Discount()->exists()) {
                 if (empty($list['id_' . $item->Discount()->ID])) {
                     $list['id_' . $item->Discount()->ID]    =   [
                         'title'     =>  $item->Discount()->Title,
                         'amount'    =>  $item->Discount()->calc_discount($item->Subtotal)
                     ];
                 } else {
                     $list['id_' . $item->Discount()->ID]['amount'] += $item->Discount()->calc_discount($item->Subtotal);
                 }
             }
         }

         return array_values($list);
     }
}

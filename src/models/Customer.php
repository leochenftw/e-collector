<?php

namespace Leochenftw\eCommerce\eCollector\Model;
use SilverStripe\Security\Member;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use Leochenftw\eCommerce\eCollector\Model\Order;
use Leochenftw\eCommerce\eCollector\Model\Address;

class Customer extends Member
{
    /**
     * Defines the database table name
     * @var string
     */
    private static $table_name = 'ECollectorCustomer';
    /**
     * Database fields
     * @var array
     */
    private static $db = [
        'Phone'             =>  'Varchar(128)',
        'ShippingAddress'   =>  'Text',
        'ShippingSuburb'    =>  'Varchar(128)',
        'ShippingTown'      =>  'Varchar(128)',
        'ShippingRegion'    =>  'Varchar(128)',
        'ShippingCountry'   =>  'Varchar(128)',
        'ShippingPostcode'  =>  'Varchar(128)',
        'BillingAddress'    =>  'Text',
        'BillingSuburb'     =>  'Varchar(128)',
        'BillingTown'       =>  'Varchar(128)',
        'BillingRegion'     =>  'Varchar(128)',
        'BillingCountry'    =>  'Varchar(128)',
        'BillingPostcode'   =>  'Varchar(128)'
    ];

    /**
     * Has_many relationship
     * @var array
     */
    private static $has_many = [
        'Orders'    =>  Order::class,
        'Addresses' =>  Address::class
    ];

    /**
     * Event handler called before writing to the database.
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if (empty($this->BillingAddress)) {
            $this->BillingAddress   =   $this->ShippingAddress;
        }

        if (empty($this->BillingSuburb)) {
            $this->BillingSuburb    =   $this->ShippingSuburb;
        }

        if (empty($this->BillingTown)) {
            $this->BillingTown      =   $this->ShippingTown;
        }

        if (empty($this->BillingRegion)) {
            $this->BillingRegion    =   $this->ShippingRegion;
        }

        if (empty($this->BillingCountry)) {
            $this->BillingCountry   =   $this->ShippingCountry;
        }

        if (empty($this->BillingPostcode)) {
            $this->BillingPostcode  =   $this->ShippingPostcode;
        }
    }

    /**
     * CMS Fields
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields     =   parent::getCMSFields();


        $fields->removeByName([
            'Locale',
            'FailedLoginCount',
            'DirectGroups',
            'Permissions'
        ]);

        $fields->addFieldsToTab(
            'Root.Shipping',
            [
                TextareaField::create('ShippingAddress', 'Address'),
                TextField::create('ShippingSuburb', 'Suburb'),
                TextField::create('ShippingTown', 'Town'),
                TextField::create('ShippingRegion', 'Region'),
                TextField::create('ShippingCountry', 'Country'),
                TextField::create('ShippingPostcode', 'Postcode'),
            ]
        );

        $fields->addFieldsToTab(
            'Root.Billing',
            [
                TextareaField::create('BillingAddress', 'Address'),
                TextField::create('BillingSuburb', 'Suburb'),
                TextField::create('BillingTown', 'Town'),
                TextField::create('BillingRegion', 'Region'),
                TextField::create('BillingCountry', 'Country'),
                TextField::create('BillingPostcode', 'Postcode'),
            ]
        );

        return $fields;
    }
}

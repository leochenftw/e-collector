<?php

namespace Leochenftw\eCommerce\eCollector\Model;
use SilverStripe\Security\Member;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use Leochenftw\eCommerce\eCollector\Model\Order;
use Leochenftw\eCommerce\eCollector\Model\Address;
use SilverStripe\Security\Group;

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

        return $fields;
    }

    /**
     * Event handler called after writing to the database.
     */
    public function onAfterWrite()
    {
        parent::onAfterWrite();
        $this->addToGroupByCode('customers', 'Customers');
    }

    public function requireDefaultRecords()
    {
        if (empty(Group::get()->filter(['Code' => 'customers'])->first())) {
            $group          =   Group::create();
            $group->Code    =   'customers';
            $group->Title   =   'Customers';
            $group->write();
        }
    }
}

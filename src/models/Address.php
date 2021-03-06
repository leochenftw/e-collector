<?php

namespace Leochenftw\eCommerce\eCollector\Model;

use SilverStripe\ORM\DataObject;
use Leochenftw\eCommerce\eCollector\Model\Customer;
use Dynamic\CountryDropdownField\Fields\CountryDropdownField;
/**
 * Description
 *
 * @package silverstripe
 * @subpackage mysite
 */
class Address extends DataObject
{
    /**
     * Defines the database table name
     * @var string
     */
    private static $table_name = 'ECollectorAddress';

    /**
     * Database fields
     * @var array
     */
    private static $db = [
        'FirstName'     =>  'Varchar(128)',
        'Surname'       =>  'Varchar(128)',
        'Email'         =>  'Varchar(512)',
        'Phone'         =>  'Varchar(32)',
        'Company'       =>  'Varchar(512)',
        'Address'       =>  'Text',
        'Apartment'     =>  'Varchar(64)',
        'Suburb'        =>  'Varchar(64)',
        'City'          =>  'Varchar(64)',
        'Region'        =>  'Varchar(64)',
        'Country'       =>  'Varchar(64)',
        'Postcode'      =>  'Varchar(16)'
    ];

    private static $indexes = [
        
    ];

    /**
     * Has_one relationship
     * @var array
     */
    private static $has_one = [
        'Customer'      =>  Customer::class
    ];
}

<?php

namespace Leochenftw\eCommerce\eCollector\Model;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Security\Group;

/**
 * Description
 *
 * @package silverstripe
 * @subpackage mysite
 */
class Discount extends DataObject
{
    /**
     * Defines the database table name
     * @var string
     */
    private static $table_name = 'ECollectorDiscount';
    /**
     * Singular name for CMS
     * @var string
     */
    private static $singular_name = 'Discount';
    /**
     * Plural name for CMS
     * @var string
     */
    private static $plural_name = 'Discounts';

    /**
     * Database fields
     * @var array
     */
    private static $db = [
        'Title'         =>  'Varchar(128)',
        'DiscountBy'    =>  'Enum("ByPercentage,ByValue")',
        'DiscountRate'  =>  'Decimal',
        'Type'          =>  'Enum("Member Type,Coupon")',
        'CouponCode'    =>  'Varchar(128)'
    ];

    private static $indexes = [
        'CouponCode' => [
            'type'      =>  'unique',
            'columns'   =>  ['CouponCode'],
        ],
    ];

    /**
     * Belongs_to relationship
     * @var array
     */
    private static $belongs_to = [
        'Group' =>  Group::class
    ];


    public function populateDefaults()
    {
        $this->CouponCode   =   strtoupper(substr(sha1(time()), 0, 8));
    }

    /**
     * CMS Fields
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields =   parent::getCMSFields();
        $coupon =   $fields->fieldByName('Root.Main.CouponCode');
        $fields->removeByName([
            'CouponCode'
        ]);

        $fields->fieldByName('Root.Main.DiscountRate')->setDescription('If "Discount By" is set to "By Percentage", it will be x% off; if set to "By Value", it will be $x off.');

        $fields->fieldByName('Root.Main.Type')->setEmptyString('- select one -');

        if ($this->Type == 'Coupon') {
            $fields->addFieldToTab(
                'Root.Main',
                $coupon
            );
        } elseif ($this->Type == 'Member Type') {
            if ($this->Group()->exists()) {
                $field  =   LiteralField::create('Group', 'This discount has been bound to <a href="/admin/security/EditForm/field/Groups/item/' . $this->Group()->ID . '/edit">' . $this->Group()->Title . '</a> group');
            } else {
                $field  =   LiteralField::create('Group', 'Please go to the <a href="/admin/security/groups">Group panel</a>, and bind the discount to the desired group');
            }

            $fields->addFieldToTab(
                'Root.Main',
                $field
            );
        }

        return $fields;
    }

    public function calc_discount($amount)
    {
        if ($this->DiscountBy == 'ByPercentage') {
            return $amount * $this->DiscountRate * 0.01;
        }

        return ($amount - $this->DiscountRate >= 0) ? $amount - $this->DiscountRate : 0;
    }

    public function getData()
    {
        return [
            'id'    =>  $this->ID,
            'title' =>  $this->Title,
            'by'    =>  $this->DiscountBy == 'ByPercentage' ? '%' : '-',
            'rate'  =>  (float) $this->DiscountRate
        ];
    }
}

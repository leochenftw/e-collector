<?php

namespace Leochenftw\eCommerce\eCollector\Model;
use Page;
use SilverStripe\Forms\CurrencyField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\FieldList;
use Leochenftw\eCommerce\eCollector\Extensions\ProductExtension;
use Leochenftw\eCommerce\eCollector\Extensions\ProductOrderItemCommonFields;

/**
 * Description
 *
 * @package silverstripe
 * @subpackage mysite
 */
class Product extends Page
{
    /**
     * Defines the database table name
     * @var string
     */
    private static $table_name = 'ECollectorProduct';

    /**
     * Defines extension names and parameters to be applied
     * to this object upon construction.
     * @var array
     */
    private static $extensions = [
        ProductOrderItemCommonFields::class,
        ProductExtension::class
    ];

    /**
     * CMS Fields
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields         =   parent::getCMSFields();
        $fields->addFieldsToTab(
            'Root.ProductDetails',
            [
                CheckboxField::create(
                    'isDigital',
                    'is Digital Product'
                )->setDescription('means no freight required'),
                CheckboxField::create(
                    'NoDiscount',
                    'This product does not accept any discout'
                ),
                CheckboxField::create(
                    'isExempt',
                    'This product is not subject to GST'
                ),
                TextField::create('SKU', 'SKU'),
                CheckboxField::create('OutOfStock', 'Out of Stock'),
                CurrencyField::create('Price'),
                TextField::create('UnitWeight')->setDescription('in KG. If you are not charging the freight cost on weight, leave it 0.00')
            ]
        );
        $this->extend('updateCMSFields', $fields);
        return $fields;
    }
}

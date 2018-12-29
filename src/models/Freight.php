<?php

namespace Leochenftw\eCommerce\eCollector\Model;
use SilverStripe\ORM\DataObject;
use SilverStripe\Assets\Image;
use Leochenftw\Debugger;

/**
 * Description
 *
 * @package silverstripe
 * @subpackage mysite
 */
class Freight extends DataObject
{
    private static $table_name = 'ECollectorFreight';

    /**
     * Database fields
     * @var array
     */
    private static $db = [
        'Title'                 =>  'Varchar(128)',
        'Website'               =>  'Varchar(1024)',
        'MeasurementUnit'       =>  'Enum("KG,Unit")',
        'BasePrice'             =>  'Currency',
        'AfterX'                =>  'Decimal',
        'Increment'             =>  'Currency',
        'ContainerPrice'        =>  'Currency',
        'ContainerCapacity'     =>  'Decimal'
    ];

    /**
     * Has_one relationship
     * @var array
     */
    private static $has_one = [
        'Logo'          =>  Image::class
    ];

    private static $owns = [ 'Logo' ];

    public function validate()
    {
        $result = parent::validate();

        if (!empty($this->Website) && strpos($this->Website, 'http://') !== 0 && strpos($this->Website, 'https://') !== 0) {
            $result->addError('The website URL that you entered is not valid. Please include http:// or https:// at the beginning.');
        }

        return $result;
    }

    private static $searchable_fields = [
        'Title'
    ];

    /**
     * Defines summary fields commonly used in table columns
     * as a quick overview of the data for this dataobject
     * @var array
     */
    private static $summary_fields = [
        'getSummaryLogo'    =>  'Logo',
        'Title'             =>  'Freight Provider',
        'getSummaryPrice'   =>  'Cost'
    ];

    public function getSummaryLogo()
    {
        if ($this->Logo()->exists()) {
            return $this->Logo()->ScaleHeight(20);
        }

        return false;
    }

    private function simple_under_x()
    {
        return ($this->MeasurementUnit == 'KG' ? $this->AfterX : strtolower(round($this->AfterX))) . ' ' . $this->MeasurementUnit . '(s)';
    }

    public function getSummaryPrice()
    {
        return '$' . $this->BasePrice . ' under ' . $this->simple_under_x() . ', and + $' . $this->Increment . ' for every additional ' . $this->MeasurementUnit . '. Container cost is $' . $this->ContainerPrice . ' for every ' . $this->ContainerCapacity . ' ' . $this->MeasurementUnit;
    }

    /**
     * CMS Fields
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields =   parent::getCMSFields();
        $logo   =   $fields->fieldByName('Root.Main.Logo');

        $fields->addFieldToTab(
            'Root.Main',
            $logo,
            'Title'
        );

        $base_price =   $fields->fieldByName('Root.Main.BasePrice');
        $base_price->setDescription('The price at or under <strong>X</strong> ' . ($this->exists() ? $this->MeasurementUnit : 'KG') . '(s)');

        $after_x    =   $fields->fieldByName('Root.Main.AfterX');
        $after_x->setTitle('After X ' . ($this->exists() ? $this->MeasurementUnit : 'KG') . '(s)')
            ->setDescription('This when the freight cost starts to increase.');

        $increment  =   $fields->fieldByName('Root.Main.Increment');
        $increment->setDescription('After X ' . ($this->exists() ? $this->MeasurementUnit : 'KG') . '(s), the freight cost is going to increase by this value.');

        $container  =   $fields->fieldByName('Root.Main.ContainerPrice');
        $container->setDescription('Some freight providers don\'t give you free containers, hence you need to include the cost here. But, you can leave it blank or 0.00');

        $capacity   =   $fields->fieldByName('Root.Main.ContainerCapacity');
        $capacity->setDescription('The capacity of the container. e.g. a container can contain 10 items/KG. When there are 9 items, it will cost the customer 1 container price cost; When there are 11 items, the customer needs to pay for 2 containers. <strong style="text-decoration: underline;">Leave it blank or 0.00 if your container is free</strong>');

        $this->extend('updateCMSFields', $fields);
        return $fields;
    }

    private function CalculateContainerCost($value)
    {
        if ($this->ContainerCapacity == 0) {
            return 0;
        }
        
        if ($this->ContainerCapacity > $value) {
            return $this->ContainerPrice;
        }

        return ceil($value / $this->ContainerCapacity) * $this->ContainerPrice;
    }

    public function CalculateCost($weight = 0, $unit = 0)
    {
        if ($this->MeasurementUnit == 'KG') {
            if ($weight <= $this->AfterX) {
                return $this->BasePrice + $this->CalculateContainerCost($weight);
            }

            return $this->BasePrice + ($weight - $this->AfterX) * $this->Increment + $this->CalculateContainerCost($weight);
        }

        if ($unit <= $this->AfterX) {
            return $this->BasePrice + $this->CalculateContainerCost($unit);
        }

        return $this->BasePrice + ($unit - $this->AfterX) * $this->Increment + $this->CalculateContainerCost($unit);
    }

}

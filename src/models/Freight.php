<?php

namespace Leochenftw\eCommerce\eCollector\Model;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\DropdownField;
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
        'TrackingPage'          =>  'Varchar(1024)',
        'MeasurementUnit'       =>  'Enum("KG,Unit")',
        'SingleItemPrice'       =>  'Currency',
        'BasePrice'             =>  'Currency',
        'AfterX'                =>  'Decimal',
        'Increment'             =>  'Currency',
        'ContainerPrice'        =>  'Currency',
        'ContainerCapacity'     =>  'Decimal',
        'MaxPrice'              =>  'Currency',
        'Zone'                  =>  'Varchar(128)'
    ];

    public function getData()
    {
        return [
            'id'        =>  $this->ID,
            'title'     =>  $this->Title,
            'url'       =>  $this->Website,
            'logo'      =>  $this->getSummaryLogo(80),
            'rate'      =>  $this->getSummaryPrice()
        ];
    }

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

        if (!empty($this->TrackingPage) && strpos($this->TrackingPage, 'http://') !== 0 && strpos($this->TrackingPage, 'https://') !== 0) {
            $result->addError('The tracking page URL that you entered is not valid. Please include http:// or https:// at the beginning.');
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

    public function getSummaryLogo($height = 20)
    {
        if ($this->Logo()->exists()) {
            return $this->Logo()->ScaleHeight($height)->getAbsoluteURL();
        }

        return false;
    }

    private function simple_under_x()
    {
        return ($this->MeasurementUnit == 'KG' ? $this->AfterX : strtolower(round($this->AfterX))) . ' ' . strtolower($this->MeasurementUnit) . ($this->ContainerCapacity > 1 ? 's' : '');
    }

    public function getSummaryPrice()
    {
        return '$' . $this->BasePrice . ' under ' . $this->simple_under_x() . ', and + $' . $this->Increment . ' for every additional ' . strtolower($this->MeasurementUnit) . '. Container cost is $' . $this->ContainerPrice . ' for every ' . ((int) ($this->ContainerCapacity)) . ' ' . strtolower($this->MeasurementUnit) . ($this->ContainerCapacity > 1 ? 's' : '');
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
            ->setDescription('This is when the freight cost starts to increase.');

        $increment  =   $fields->fieldByName('Root.Main.Increment');
        $increment->setDescription('After X ' . ($this->exists() ? $this->MeasurementUnit : 'KG') . '(s), the freight cost is going to increase by this value.');

        $container  =   $fields->fieldByName('Root.Main.ContainerPrice');
        $container->setDescription('Some freight providers don\'t give you free containers, hence you need to include the cost here. But, you can leave it blank or 0.00');

        $capacity   =   $fields->fieldByName('Root.Main.ContainerCapacity');
        $capacity->setDescription('The capacity of the container. e.g. a container can contain 10 items/KG. When there are 9 items, it will cost the customer 1 container price cost; When there are 11 items, the customer needs to pay for 2 containers. <strong style="text-decoration: underline;">Leave it blank or 0.00 if your container is free</strong>');
        $fields->fieldByName('Root.Main.SingleItemPrice')->setDescription('This is used in Measurement Unit: Unit');
        $fields->addFieldToTab(
            'Root.Main',
            DropdownField::create(
                'Zone',
                'Zone',
                $this->list_zones()
            ),
            'SingleItemPrice'
        );

        $this->extend('updateCMSFields', $fields);
        return $fields;
    }

    private function list_zones()
    {
        $list   =   [];
        $zones  =   array_keys($this->config()->allowed_countries);
        foreach ($zones as $zone) {
            $list[$zone]    =   $zone;
        }

        return $list;
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

        $sum    =   $this->BasePrice + ($unit - $this->AfterX) * $this->Increment + $this->CalculateContainerCost($unit);
        if ($sum > $this->MaxPrice) {
            $sum    =   $this->MaxPrice;
        }

        return $sum;
    }

    public function CalculateOrderCost(&$order)
    {
        if ($this->MeasurementUnit == 'KG') {
            $weight =   $order->TotalWeight;
            if ($weight <= $this->AfterX) {
                return $this->BasePrice + $this->CalculateContainerCost($weight);
            }

            return $this->BasePrice + ($weight - $this->AfterX) * $this->Increment + $this->CalculateContainerCost($weight);
        }

        $unit =   $order->ShippableItemCount();

        if ($unit == 1) {
            return $this->SingleItemPrice;
        }

        $sum    =   $this->BasePrice + ($unit - $this->AfterX) * $this->Increment + $this->CalculateContainerCost($unit);
        if ($sum > $this->MaxPrice) {
            $sum    =   $this->MaxPrice;
        }

        return $sum;
    }

    public static function find_zone($country)
    {
        $zones  =   Config::inst()->get(__CLASS__, 'allowed_countries');
        foreach ($zones as $key => $zone) {
            foreach ($zone as $code => $name) {
                if ($code == $country) {
                    return $key;
                }
            }
        }

        return null;
    }

    public static function get_allowed_countries()
    {
        $zones      =   Config::inst()->get(__CLASS__, 'allowed_countries');
        $countries  =   [];
        foreach ($zones as $key => $zone) {
            foreach ($zone as $code => $name) {
                $countries[]    =   $code;
            }
        }

        return $countries;
    }

    public static function find_zone_freight($country)
    {
        $zone       =   static::find_zone($country);
        return Freight::get()->filter(['Zone' => $zone])->first();
    }
}

<?php

namespace Leochenftw\eCommerce\eCollector\Extensions;

use SilverStripe\Forms\EmailField;
use SilverStripe\Assets\Image;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;

class SiteconfigExtension extends DataExtension
{
    /**
     * Database fields
     * @var array
     */
    private static $db = [
        'TradingName'   =>  'Varchar(128)',
        'GST'           =>  'Varchar(32)',
        'StoreLocation' =>  'Text',
        'ContactNumber' =>  'Varchar(16)',
        'ContactEmail'  =>  'Varchar(256)'
    ];
    /**
     * Has_one relationship
     * @var array
     */
    private static $has_one = [
        'StoreLogo' =>  Image::class
    ];

    /**
     * Relationship version ownership
     * @var array
     */
    private static $owns = [
        'StoreLogo'
    ];

    /**
     * Update Fields
     * @return FieldList
     */
    public function updateCMSFields(FieldList $fields)
    {
        $owner = $this->owner;
        $fields->addFieldsToTab(
            'Root.StoreInformation',
            [
                UploadField::create(
                    'StoreLogo',
                    'Store Logo'
                ),
                TextField::create('TradingName'),
                TextField::create('GST'),
                TextField::create('ContactNumber', 'Store Phone Number'),
                EmailField::create('ContactEmail', 'Store Email'),
                TextareaField::create('StoreLocation', 'Store Location')
            ]
        );
        return $fields;
    }

    public function get_store_data()
    {
        $logo   =   $this->owner->StoreLogo();
        return [
            'store_logo'    =>  $logo->exists() ? $logo->ScaleHeight(80)->getAbsoluteURL() : null,
            'title'         =>  $this->owner->TradingName,
            'gst'           =>  $this->owner->GST,
            'phone'         =>  $this->owner->ContactNumber,
            'email'         =>  $this->owner->ContactEmail,
            'location'      =>  $this->owner->StoreLocation
        ];
    }
}

<?php

namespace Leochenftw\eCommerce\eCollector\Payment;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\DropdownField;
use Leochenftw\eCommerce\eCollector\Model\Order;

/**
 * Description
 *
 * @package silverstripe
 * @subpackage mysite
 */
class Payment extends DataObject
{
    /**
     * Defines the database table name
     * @var string
     */
    private static $table_name = 'ECollectorPayment';

    private static $indexes = [
        'TransacID'     =>  true,
        'TransacID'     =>  [
                                        'type'      =>  'index',
                                        'columns'   =>  ['TransacID']
                                    ]
    ];

    /**
     * @config
     */
    private static $payment_methods =   [];

    /**
     * Database fields
     * @var array
     */
    private static $db = [
        'PaymentMethod'         =>  'Varchar(32)',
        'CardType'              =>  'Varchar(16)',
        'CardNumber'            =>  'Varchar(32)',
        'PayerAccountNumber'    =>  'Varchar(128)', // Poli bank account payment only
        'PayerAccountSortCode'  =>  'Varchar(64)',  // Poli bank account payment only
        'PayerBankName'         =>  'Varchar(128)', // Poli bank account payment only
        'CardHolder'            =>  'Varchar(128)',
        'Expiry'                =>  'Varchar(8)',
        'TransacID'             =>  'Varchar(128)',
        'Status'                =>  "Enum('Incomplete,Success,Failed,Pending,Invoice Pending,Cancelled,CardSavedOnly','Incomplete')",
        'Amount'                =>  'Currency',
        'Message'               =>  'Text',
        'IP'                    =>  'Varchar'
    ];

    /**
     * Defines summary fields commonly used in table columns
     * as a quick overview of the data for this dataobject
     * @var array
     */
    private static $summary_fields = [
        'PaymentMethod'         =>  'Payment Method',
        'Status'                =>  'Status',
        'CardDispaly'           =>  'Card Number',
        'Amount'                =>  'Amount',
        'Created'               =>  'At'
    ];

    /**
     * Defines a default list of filters for the search context
     * @var array
     */
    private static $searchable_fields = [
        'PaymentMethod',
        'Status',
        'Created'
    ];

    /**
     * Default sort ordering
     * @var array
     */
    private static $default_sort = ['ID' => 'DESC'];

    /**
     * Has_one relationship
     * @var array
     */
    private static $has_one = [
        'Order'                 =>  Order::class
    ];

    /**
     * CMS Fields
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields =   parent::getCMSFields();
        $fields->addFieldToTab(
            'Root.Main',
            DropdownField::create(
                'PaymentMethod',
                'Payment Method',
                $this->config()->payment_methods
            )->setEmptyString('- select one -'),
            'TransacID'
        );

        if (empty($this->PaymentMethod)) {
            $fields->removeByName([
                'CardType',
                'CardNumber',
                'PayerAccountNumber',
                'PayerAccountSortCode',
                'PayerBankName',
                'CardHolder',
                'Expiry'
            ]);
        } elseif ($this->PaymentMethod == 'POLi') {
            $fields->removeByName([
                'CardType',
                'CardNumber',
                'Expiry'
            ]);
        } else {
            $fields->removeByName([
                'PayerAccountNumber',
                'PayerAccountSortCode',
                'PayerBankName'
            ]);
        }

        return $fields;
    }

    public function CardDispaly()
    {
        return  $this->PaymentMethod == 'POLi' ?
                (!empty($this->PayerAccountNumber) ? ( $this->PayerAccountNumber . ', ' . $this->PayerBankName) : 'N/A') :
                $this->CardNumber;
    }

    public function getData()
    {
        return [
            'id'                =>  $this->ID,
            'transaction_id'    =>  $this->TransacID,
            'created'           =>  $this->Created,
            'status'            =>  $this->Status,
            'amount'            =>  $this->Amount,
            'payment_method'    =>  $this->PaymentMethod,
            'card_type'         =>  $this->CardType,
            'card_number'       =>  $this->CardNumber,
            'card_holder'       =>  $this->CardHolder,
            'card_expiry'       =>  $this->Expiry
        ];
    }
}

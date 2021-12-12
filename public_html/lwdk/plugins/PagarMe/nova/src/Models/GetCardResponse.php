<?php
/*
 * PagarmeCoreApiLib
 *
 * This file was automatically generated by APIMATIC v2.0 ( https://apimatic.io ).
 */

namespace PagarmeCoreApiLib\Models;

use JsonSerializable;
use PagarmeCoreApiLib\Utils\DateTimeHelper;

/**
 *Response object for getting a credit card
 */
class GetCardResponse implements JsonSerializable
{
    /**
     * @todo Write general description for this property
     * @required
     * @var string $id public property
     */
    public $id;

    /**
     * @todo Write general description for this property
     * @required
     * @maps last_four_digits
     * @var string $lastFourDigits public property
     */
    public $lastFourDigits;

    /**
     * @todo Write general description for this property
     * @required
     * @var string $brand public property
     */
    public $brand;

    /**
     * @todo Write general description for this property
     * @required
     * @maps holder_name
     * @var string $holderName public property
     */
    public $holderName;

    /**
     * @todo Write general description for this property
     * @required
     * @maps exp_month
     * @var integer $expMonth public property
     */
    public $expMonth;

    /**
     * @todo Write general description for this property
     * @required
     * @maps exp_year
     * @var integer $expYear public property
     */
    public $expYear;

    /**
     * @todo Write general description for this property
     * @required
     * @var string $status public property
     */
    public $status;

    /**
     * @todo Write general description for this property
     * @required
     * @maps created_at
     * @factory \PagarmeCoreApiLib\Utils\DateTimeHelper::fromRfc3339DateTime
     * @var \DateTime $createdAt public property
     */
    public $createdAt;

    /**
     * @todo Write general description for this property
     * @required
     * @maps updated_at
     * @factory \PagarmeCoreApiLib\Utils\DateTimeHelper::fromRfc3339DateTime
     * @var \DateTime $updatedAt public property
     */
    public $updatedAt;

    /**
     * @todo Write general description for this property
     * @required
     * @maps billing_address
     * @var \PagarmeCoreApiLib\Models\GetBillingAddressResponse $billingAddress public property
     */
    public $billingAddress;

    /**
     * @todo Write general description for this property
     * @var \PagarmeCoreApiLib\Models\GetCustomerResponse|null $customer public property
     */
    public $customer;

    /**
     * @todo Write general description for this property
     * @required
     * @var array $metadata public property
     */
    public $metadata;

    /**
     * Card type
     * @required
     * @var string $type public property
     */
    public $type;

    /**
     * Document number for the card's holder
     * @required
     * @maps holder_document
     * @var string $holderDocument public property
     */
    public $holderDocument;

    /**
     * @todo Write general description for this property
     * @maps deleted_at
     * @factory \PagarmeCoreApiLib\Utils\DateTimeHelper::fromRfc3339DateTime
     * @var \DateTime|null $deletedAt public property
     */
    public $deletedAt;

    /**
     * First six digits
     * @required
     * @maps first_six_digits
     * @var string $firstSixDigits public property
     */
    public $firstSixDigits;

    /**
     * @todo Write general description for this property
     * @required
     * @var string $label public property
     */
    public $label;

    /**
     * Constructor to set initial or default values of member properties
     * @param string                     $id             Initialization value for $this->id
     * @param string                     $lastFourDigits Initialization value for $this->lastFourDigits
     * @param string                     $brand          Initialization value for $this->brand
     * @param string                     $holderName     Initialization value for $this->holderName
     * @param integer                    $expMonth       Initialization value for $this->expMonth
     * @param integer                    $expYear        Initialization value for $this->expYear
     * @param string                     $status         Initialization value for $this->status
     * @param \DateTime                  $createdAt      Initialization value for $this->createdAt
     * @param \DateTime                  $updatedAt      Initialization value for $this->updatedAt
     * @param GetBillingAddressResponse  $billingAddress Initialization value for $this->billingAddress
     * @param GetCustomerResponse        $customer       Initialization value for $this->customer
     * @param array                      $metadata       Initialization value for $this->metadata
     * @param string                     $type           Initialization value for $this->type
     * @param string                     $holderDocument Initialization value for $this->holderDocument
     * @param \DateTime                  $deletedAt      Initialization value for $this->deletedAt
     * @param string                     $firstSixDigits Initialization value for $this->firstSixDigits
     * @param string                     $label          Initialization value for $this->label
     */
    public function __construct()
    {
        if (17 == func_num_args()) {
            $this->id             = func_get_arg(0);
            $this->lastFourDigits = func_get_arg(1);
            $this->brand          = func_get_arg(2);
            $this->holderName     = func_get_arg(3);
            $this->expMonth       = func_get_arg(4);
            $this->expYear        = func_get_arg(5);
            $this->status         = func_get_arg(6);
            $this->createdAt      = func_get_arg(7);
            $this->updatedAt      = func_get_arg(8);
            $this->billingAddress = func_get_arg(9);
            $this->customer       = func_get_arg(10);
            $this->metadata       = func_get_arg(11);
            $this->type           = func_get_arg(12);
            $this->holderDocument = func_get_arg(13);
            $this->deletedAt      = func_get_arg(14);
            $this->firstSixDigits = func_get_arg(15);
            $this->label          = func_get_arg(16);
        }
    }


    /**
     * Encode this object to JSON
     */
    public function jsonSerialize()
    {
        $json = array();
        $json['id']               = $this->id;
        $json['last_four_digits'] = $this->lastFourDigits;
        $json['brand']            = $this->brand;
        $json['holder_name']      = $this->holderName;
        $json['exp_month']        = $this->expMonth;
        $json['exp_year']         = $this->expYear;
        $json['status']           = $this->status;
        $json['created_at']       = DateTimeHelper::toRfc3339DateTime($this->createdAt);
        $json['updated_at']       = DateTimeHelper::toRfc3339DateTime($this->updatedAt);
        $json['billing_address']  = $this->billingAddress;
        $json['customer']         = $this->customer;
        $json['metadata']         = $this->metadata;
        $json['type']             = $this->type;
        $json['holder_document']  = $this->holderDocument;
        $json['deleted_at']       = isset($this->deletedAt) ?
            DateTimeHelper::toRfc3339DateTime($this->deletedAt) : null;
        $json['first_six_digits'] = $this->firstSixDigits;
        $json['label']            = $this->label;

        return $json;
    }
}

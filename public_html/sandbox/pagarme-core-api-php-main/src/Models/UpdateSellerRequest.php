<?php
/*
 * PagarmeCoreApiLib
 *
 * This file was automatically generated by APIMATIC v2.0 ( https://apimatic.io ).
 */

namespace PagarmeCoreApiLib\Models;

use JsonSerializable;

/**
 * @todo Write general description for this model
 */
class UpdateSellerRequest implements JsonSerializable
{
    /**
     * Seller name
     * @required
     * @var string $name public property
     */
    public $name;

    /**
     * Seller code
     * @required
     * @var string $code public property
     */
    public $code;

    /**
     * Seller description
     * @required
     * @var string $description public property
     */
    public $description;

    /**
     * Seller document CPF or CNPJ
     * @required
     * @var string $document public property
     */
    public $document;

    /**
     * @todo Write general description for this property
     * @required
     * @var string $status public property
     */
    public $status;

    /**
     * @todo Write general description for this property
     * @required
     * @var string $type public property
     */
    public $type;

    /**
     * @todo Write general description for this property
     * @required
     * @var \PagarmeCoreApiLib\Models\CreateAddressRequest $address public property
     */
    public $address;

    /**
     * @todo Write general description for this property
     * @required
     * @var array $metadata public property
     */
    public $metadata;

    /**
     * Constructor to set initial or default values of member properties
     * @param string               $name        Initialization value for $this->name
     * @param string               $code        Initialization value for $this->code
     * @param string               $description Initialization value for $this->description
     * @param string               $document    Initialization value for $this->document
     * @param string               $status      Initialization value for $this->status
     * @param string               $type        Initialization value for $this->type
     * @param CreateAddressRequest $address     Initialization value for $this->address
     * @param array                $metadata    Initialization value for $this->metadata
     */
    public function __construct()
    {
        if (8 == func_num_args()) {
            $this->name        = func_get_arg(0);
            $this->code        = func_get_arg(1);
            $this->description = func_get_arg(2);
            $this->document    = func_get_arg(3);
            $this->status      = func_get_arg(4);
            $this->type        = func_get_arg(5);
            $this->address     = func_get_arg(6);
            $this->metadata    = func_get_arg(7);
        }
    }


    /**
     * Encode this object to JSON
     */
    public function jsonSerialize()
    {
        $json = array();
        $json['name']        = $this->name;
        $json['code']        = $this->code;
        $json['description'] = $this->description;
        $json['document']    = $this->document;
        $json['status']      = $this->status;
        $json['type']        = $this->type;
        $json['address']     = $this->address;
        $json['metadata']    = $this->metadata;

        return $json;
    }
}

<?php
/*
 * PagarmeCoreApiLib
 *
 * This file was automatically generated by APIMATIC v2.0 ( https://apimatic.io ).
 */

namespace PagarmeCoreApiLib\Models;

use JsonSerializable;

/**
 *Response object for getting an order item
 */
class GetOrderItemResponse implements JsonSerializable
{
    /**
     * Id
     * @required
     * @var string $id public property
     */
    public $id;

    /**
     * @todo Write general description for this property
     * @required
     * @var integer $amount public property
     */
    public $amount;

    /**
     * @todo Write general description for this property
     * @required
     * @var string $description public property
     */
    public $description;

    /**
     * @todo Write general description for this property
     * @required
     * @var integer $quantity public property
     */
    public $quantity;

    /**
     * Seller data
     * @maps GetSellerResponse
     * @var \PagarmeCoreApiLib\Models\GetSellerResponse|null $getSellerResponse public property
     */
    public $getSellerResponse;

    /**
     * Category
     * @required
     * @var string $category public property
     */
    public $category;

    /**
     * Code
     * @required
     * @var string $code public property
     */
    public $code;

    /**
     * Constructor to set initial or default values of member properties
     * @param string            $id                Initialization value for $this->id
     * @param integer           $amount            Initialization value for $this->amount
     * @param string            $description       Initialization value for $this->description
     * @param integer           $quantity          Initialization value for $this->quantity
     * @param GetSellerResponse $getSellerResponse Initialization value for $this->getSellerResponse
     * @param string            $category          Initialization value for $this->category
     * @param string            $code              Initialization value for $this->code
     */
    public function __construct()
    {
        if (7 == func_num_args()) {
            $this->id                = func_get_arg(0);
            $this->amount            = func_get_arg(1);
            $this->description       = func_get_arg(2);
            $this->quantity          = func_get_arg(3);
            $this->getSellerResponse = func_get_arg(4);
            $this->category          = func_get_arg(5);
            $this->code              = func_get_arg(6);
        }
    }


    /**
     * Encode this object to JSON
     */
    public function jsonSerialize()
    {
        $json = array();
        $json['id']                = $this->id;
        $json['amount']            = $this->amount;
        $json['description']       = $this->description;
        $json['quantity']          = $this->quantity;
        $json['GetSellerResponse'] = $this->getSellerResponse;
        $json['category']          = $this->category;
        $json['code']              = $this->code;

        return $json;
    }
}

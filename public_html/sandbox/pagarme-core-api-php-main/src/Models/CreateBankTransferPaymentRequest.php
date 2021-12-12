<?php
/*
 * PagarmeCoreApiLib
 *
 * This file was automatically generated by APIMATIC v2.0 ( https://apimatic.io ).
 */

namespace PagarmeCoreApiLib\Models;

use JsonSerializable;

/**
 *Request for creating a bank transfer payment
 */
class CreateBankTransferPaymentRequest implements JsonSerializable
{
    /**
     * Bank
     * @required
     * @var string $bank public property
     */
    public $bank;

    /**
     * Number of retries
     * @required
     * @var integer $retries public property
     */
    public $retries;

    /**
     * Constructor to set initial or default values of member properties
     * @param string  $bank    Initialization value for $this->bank
     * @param integer $retries Initialization value for $this->retries
     */
    public function __construct()
    {
        if (2 == func_num_args()) {
            $this->bank    = func_get_arg(0);
            $this->retries = func_get_arg(1);
        }
    }


    /**
     * Encode this object to JSON
     */
    public function jsonSerialize()
    {
        $json = array();
        $json['bank']    = $this->bank;
        $json['retries'] = $this->retries;

        return $json;
    }
}

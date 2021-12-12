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
 *Response object for getting a safety pay transaction
 *
 * @discriminator transaction_type
 * @discriminatorType safetypay
 */
class GetSafetyPayTransactionResponse extends GetTransactionResponse implements JsonSerializable
{
    /**
     * Payment url
     * @required
     * @var string $url public property
     */
    public $url;

    /**
     * Transaction identifier on bank
     * @required
     * @maps bank_tid
     * @var string $bankTid public property
     */
    public $bankTid;

    /**
     * Payment date
     * @maps paid_at
     * @factory \PagarmeCoreApiLib\Utils\DateTimeHelper::fromRfc3339DateTime
     * @var \DateTime|null $paidAt public property
     */
    public $paidAt;

    /**
     * Paid amount
     * @maps paid_amount
     * @var integer|null $paidAmount public property
     */
    public $paidAmount;

    /**
     * Constructor to set initial or default values of member properties
     * @param string    $url        Initialization value for $this->url
     * @param string    $bankTid    Initialization value for $this->bankTid
     * @param \DateTime $paidAt     Initialization value for $this->paidAt
     * @param integer   $paidAmount Initialization value for $this->paidAmount
     */
    public function __construct()
    {
        if (4 == func_num_args()) {
            $this->url        = func_get_arg(0);
            $this->bankTid    = func_get_arg(1);
            $this->paidAt     = func_get_arg(2);
            $this->paidAmount = func_get_arg(3);
        }
    }


    /**
     * Encode this object to JSON
     */
    public function jsonSerialize()
    {
        $json = array();
        $json['url']         = $this->url;
        $json['bank_tid']    = $this->bankTid;
        $json['paid_at']     = isset($this->paidAt) ?
            DateTimeHelper::toRfc3339DateTime($this->paidAt) : null;
        $json['paid_amount'] = $this->paidAmount;
        $json = array_merge($json, parent::jsonSerialize());

        return $json;
    }
}

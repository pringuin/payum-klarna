<?php

namespace Pringuin\Payum\KlarnaCO\Request\Api;

use Klarna\Rest\OrderManagement\Order;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Request\Generic;

abstract class BaseOrderManagementOrder extends Generic
{
    /**
     * @var Order
     */
    protected $order;

    public function __construct($model)
    {
        if (false === (is_array($model) || $model instanceof \ArrayAccess)) {
            throw new InvalidArgumentException('Given model is invalid. Should be an array or ArrayAccess instance.');
        }

        parent::__construct($model);
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Order $order
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
    }
}

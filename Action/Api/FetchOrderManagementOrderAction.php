<?php

namespace Pringuin\Payum\KlarnaCO\Action\Api;

use Klarna\Rest\OrderManagement\Order;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Pringuin\Payum\KlarnaCO\Request\Api\FetchOrderManagementOrder;

class FetchOrderManagementOrderAction extends BaseApiAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @param FetchOrderManagementOrder $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false === $model['order_id']) {
            throw new LogicException('Order ID has to be provided to fetch an order');
        }

        $order = new Order($this->getConnector(), $model['order_id']);
        $order->fetch();

        $request->setOrder($order);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof FetchOrderManagementOrder;
    }
}

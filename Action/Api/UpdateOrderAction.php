<?php

namespace Pringuin\Payum\KlarnaCO\Action\Api;

use Klarna\Rest\Checkout\Order;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Pringuin\Payum\KlarnaCO\Request\Api\CreateOrder;
use Pringuin\Payum\KlarnaCO\Request\Api\UpdateOrder;

class UpdateOrderAction extends BaseApiAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @param CreateOrder $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $order = new Order($this->getConnector(), $model['order_id']);

        $data = $model->toUnsafeArray();
        unset($data['order_id']);

        $order->update($data);

        $request->setOrder($order);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof UpdateOrder;
    }
}

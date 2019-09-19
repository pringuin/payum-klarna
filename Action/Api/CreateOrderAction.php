<?php

namespace Pringuin\Payum\KlarnaCO\Action\Api;

use Klarna\Rest\Checkout\Order;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Pringuin\Payum\KlarnaCO\Request\Api\CreateOrder;

class CreateOrderAction extends BaseApiAwareAction
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

        $checkout = new Order($this->getConnector());
        $checkout->create($model->toUnsafeArray());

        $request->setOrder($checkout);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof CreateOrder;
    }
}

<?php

namespace Pringuin\Payum\KlarnaCO\Action\Api;

use Klarna\Rest\OrderManagement\Order;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Pringuin\Payum\KlarnaCO\Request\Api\AckOrder;
use Pringuin\Payum\KlarnaCO\Request\Api\FetchOrder;
use Pringuin\Payum\KlarnaCO\Request\Api\FetchOrderManagementOrder;

class AckOrderAction extends BaseApiAwareAction implements GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param AckOrder $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false === $model['order_id']) {
            throw new LogicException('Order ID has to be provided to fetch an order');
        }

        $order = new Order($this->getConnector(), $model['order_id']);
        $order->acknowledge();

        $request->setOrder($order);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof AckOrder;
    }
}

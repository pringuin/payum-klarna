<?php

namespace Pringuin\Payum\KlarnaCO\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Sync;
use Pringuin\Payum\KlarnaCO\Constants;
use Pringuin\Payum\KlarnaCO\Request\Api\UpdateOrder;

class NotifyAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param Notify $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute(new Sync($details));

        if (Constants::STATUS_CHECKOUT_COMPLETE === $details['status']) {
            $this->gateway->execute(new UpdateOrder(array(
                'order_id' => $details['order_id'],
                'status' => Constants::STATUS_CREATED
            )));

            $this->gateway->execute(new Sync($details));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Notify &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}

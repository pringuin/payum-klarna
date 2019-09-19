<?php

namespace Pringuin\Payum\KlarnaCO\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Sync;
use Pringuin\Payum\KlarnaCO\Constants;
use Pringuin\Payum\KlarnaCO\Request\Api\FetchOrder;
use Pringuin\Payum\KlarnaCO\Request\Api\FetchOrderManagementOrder;

class SyncAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param Sync $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model['order_id']) {
            $this->gateway->execute($fetchOrder = new FetchOrder($model));

            $model->replace($fetchOrder->getOrder()->getArrayCopy());

            if ($model['status'] === Constants::STATUS_CHECKOUT_COMPLETE) {
                $this->gateway->execute($fetchOrder = new FetchOrderManagementOrder($model));

                $order = $fetchOrder->getOrder()->getArrayCopy();

                $model->replace([
                    'order_status' => $order['status'],
                    'fraud_status' => $order['fraud_status'],
                ]);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Sync &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}

<?php

namespace Pringuin\Payum\KlarnaCO\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\RenderTemplate;
use Payum\Core\Request\Sync;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Pringuin\Payum\KlarnaCO\Action\Api\AckOrderAction;
use Pringuin\Payum\KlarnaCO\Config;
use Pringuin\Payum\KlarnaCO\Constants;
use Pringuin\Payum\KlarnaCO\Request\Api\AckOrder;
use Pringuin\Payum\KlarnaCO\Request\Api\CaptureOrder;
use Pringuin\Payum\KlarnaCO\Request\Api\CreateOrder;

/**
 * @property Config $api
 */
class AuthorizeAction implements ActionInterface, GatewayAwareInterface, GenericTokenFactoryAwareInterface, ApiAwareInterface
{
    use ApiAwareTrait;
    use GatewayAwareTrait;
    use GenericTokenFactoryAwareTrait;

    /**
     * @var string
     */
    protected $templateName;

    /**
     * @param string|null $templateName
     */
    public function __construct($templateName)
    {
        $this->templateName = $templateName;
        $this->apiClass = Config::class;
    }

    /**
     * {@inheritDoc}
     *
     * @param Authorize $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $merchant = ArrayObject::ensureArrayObject($model['merchant_urls'] ?: []);

        if (false == $merchant['checkout'] && $this->api->checkoutUri) {
            $merchant['checkout'] = $this->api->checkoutUri;
        }

        if (false == $merchant['terms'] && $this->api->termsUri) {
            $merchant['terms'] = $this->api->termsUri;
        }

        if (false == $merchant['confirmation'] && $request->getToken()) {
            $merchant['confirmation'] = $request->getToken()->getTargetUrl();
        }

        if (empty($merchant['push']) && $request->getToken() && $this->tokenFactory) {
            $notifyToken = $this->tokenFactory->createNotifyToken(
                $request->getToken()->getGatewayName(),
                $request->getToken()->getDetails()
            );

            $merchant['push'] = $notifyToken->getTargetUrl();
        }

        $merchant->validateNotEmpty(['checkout', 'terms', 'confirmation', 'push']);
        $model['merchant_urls'] = (array) $merchant;

        if (false == $model['order_id']) {
            $createOrderRequest = new CreateOrder($model);
            $this->gateway->execute($createOrderRequest);

            $model->replace($createOrderRequest->getOrder()->getArrayCopy());
            $model['order_id'] = $createOrderRequest->getOrder()->getId();
        }

        $this->gateway->execute(new Sync($model));

        if (Constants::STATUS_CHECKOUT_INCOMPLETE === $model['status']) {
            $renderTemplate = new RenderTemplate($this->templateName, array(
                'snippet' => $model['html_snippet'],
            ));
            $this->gateway->execute($renderTemplate);

            throw new HttpResponse($renderTemplate->getResult());
        }

        if (Constants::STATUS_CHECKOUT_COMPLETE === $model['status']) {
            $this->gateway->execute(new AckOrder($model));
            $this->gateway->execute(new CaptureOrder($model));
            $this->gateway->execute(new Sync($model));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Authorize &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}

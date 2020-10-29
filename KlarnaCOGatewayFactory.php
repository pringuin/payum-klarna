<?php

namespace Pringuin\Payum\KlarnaCO;

use Klarna\Rest\Transport\ConnectorInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Pringuin\Payum\KlarnaCO\Action\Api\AckOrderAction;
use Pringuin\Payum\KlarnaCO\Action\Api\CaptureOrderAction;
use Pringuin\Payum\KlarnaCO\Action\Api\CreateOrderAction;
use Pringuin\Payum\KlarnaCO\Action\Api\FetchOrderAction;
use Pringuin\Payum\KlarnaCO\Action\Api\FetchOrderManagementOrderAction;
use Pringuin\Payum\KlarnaCO\Action\Api\UpdateOrderAction;
use Pringuin\Payum\KlarnaCO\Action\AuthorizeAction;
use Pringuin\Payum\KlarnaCO\Action\ConvertPaymentAction;
use Pringuin\Payum\KlarnaCO\Action\NotifyAction;
use Pringuin\Payum\KlarnaCO\Action\StatusAction;
use Pringuin\Payum\KlarnaCO\Action\SyncAction;

class KlarnaCOGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        if (!class_exists('\Klarna\Rest\Transport\Connector')) {
            throw new \LogicException('You must install "klarna/kco_rest" library.');
        }

        $config->defaults(array(
            'payum.factory_name' => 'klarna_co',
            'payum.factory_title' => 'Klarna CO',
            'payum.template.authorize' => '@PayumKlarnaCO/Action/capture.html.twig',
            'sandbox' => true,
        ));

        $config->defaults(array(
            'payum.action.authorize' => new AuthorizeAction($config['payum.template.authorize']),

            'payum.action.notify' => new NotifyAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.sync' => new SyncAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),

            'payum.action.api.create_order' => new CreateOrderAction(),
            'payum.action.api.update_order' => new UpdateOrderAction(),
            'payum.action.api.fetch_order' => new FetchOrderAction(),
            'payum.action.api.ack_order' => new AckOrderAction(),
            'payum.action.api.capture_order' => new CaptureOrderAction(),

            'payum.action.api_order_management.fetch_order' => new FetchOrderManagementOrderAction(),
        ));

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = array(
                'merchant_id' => '',
                'secret' => '',
                'terms_uri' => '',
                'checkout_uri' => '',
                'sandbox' => true,
            );
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = array('merchant_id', 'secret');

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                $klarnaConfig = new Config();
                $klarnaConfig->merchantId = $config['merchant_id'];
                $klarnaConfig->secret = $config['secret'];
                $klarnaConfig->termsUri = $config['termsUri'] ?: $config['terms_uri'];
                $klarnaConfig->checkoutUri = $config['checkoutUri'] ?: $config['checkout_uri'];
                $klarnaConfig->baseUri = $config['sandbox'] ?
                    ConnectorInterface::EU_TEST_BASE_URL :
                    ConnectorInterface::EU_BASE_URL;

                return $klarnaConfig;
            };
        }

        $config['payum.paths'] = array_replace([
            'PayumKlarnaCO' => __DIR__.'/Resources/views',
        ], $config['payum.paths'] ?: []);
    }
}

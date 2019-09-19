<?php

namespace Pringuin\Payum\KlarnaCO\Action\Api;

use Klarna\Rest\Transport\ConnectorInterface;
use Klarna\Rest\Transport\GuzzleConnector;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Pringuin\Payum\KlarnaCO\Config;

/**
 * @property Config $api
 */
abstract class BaseApiAwareAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    /**
     * @var ConnectorInterface
     */
    protected $connector;

    public function __construct()
    {
        $this->apiClass = Config::class;
    }

    /**
     * @return ConnectorInterface
     */
    protected function getConnector()
    {
        if (!$this->connector) {
            $this->connector = GuzzleConnector::create(
                $this->api->merchantId,
                $this->api->secret,
                $this->api->baseUri
            );
        }

        return $this->connector;
    }
}

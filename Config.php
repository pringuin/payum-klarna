<?php

namespace Pringuin\Payum\KlarnaCO;

use Klarna\Rest\Transport\ConnectorInterface;

class Config
{
    /**
     * @var string
     */
    public $merchantId;

    /**
     * @var string
     */
    public $secret;

    /**
     * @var int
     */
    public $baseUri = ConnectorInterface::EU_TEST_BASE_URL;

    /**
     * @var string
     */
    public $termsUri;

    /**
     * @var string
     */
    public $checkoutUri;
}

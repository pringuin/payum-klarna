<?php

namespace Pringuin\Payum\KlarnaCO\Request\Api;

use Klarna\Rest\OrderManagement\Capture;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Request\Generic;

class CaptureOrder extends Generic
{
    /**
     * @var Capture
     */
    protected $capture;

    public function __construct($model)
    {
        if (false === (is_array($model) || $model instanceof \ArrayAccess)) {
            throw new InvalidArgumentException('Given model is invalid. Should be an array or ArrayAccess instance.');
        }

        parent::__construct($model);
    }

    /**
     * @return Capture
     */
    public function getCapture(): Capture
    {
        return $this->capture;
    }

    /**
     * @param Capture $capture
     */
    public function setCapture(Capture $capture): void
    {
        $this->capture = $capture;
    }
}

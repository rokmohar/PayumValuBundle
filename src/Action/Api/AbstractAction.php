<?php

namespace RokMohar\PayumValuBundle\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use RokMohar\PayumValuBundle\ValuApi;

abstract class AbstractAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use GatewayAwareTrait;
    use ApiAwareTrait;

    /**
     * AbstractAction constructor.
     */
    public function __construct()
    {
        $this->apiClass = ValuApi::class;
    }

    /**
     * @return ValuApi
     */
    public function getApi(): ValuApi
    {
        return $this->api;
    }
}

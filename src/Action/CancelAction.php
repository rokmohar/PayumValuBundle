<?php

namespace RokMohar\PayumValuBundle\Action;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Cancel;
use RokMohar\PayumValuBundle\ValuApi;

class CancelAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $this->doExecute($request);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request): bool
    {
        return $request instanceof Cancel && $request->getModel() instanceof ArrayAccess;
    }

    /**
     * @param Cancel $request
     */
    private function doExecute(Cancel $request): void
    {
        $model = ArrayObject::ensureArrayObject($request->getModel());
        $model->offsetSet('status', ValuApi::STATUS_REJECTED);
    }
}

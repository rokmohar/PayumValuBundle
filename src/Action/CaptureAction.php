<?php

namespace RokMohar\PayumValuBundle\Action;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\Security\TokenInterface;
use RokMohar\PayumValuBundle\Request\Api\CreateCapture;

class CaptureAction implements ActionInterface, GatewayAwareInterface
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
        return $request instanceof Capture && $request->getModel() instanceof ArrayAccess;
    }

    /**
     * @param Capture $request
     */
    private function doExecute(Capture $request): void
    {
        /** @var TokenInterface $token */
        $token = $request->getToken();

        $this->gateway->execute(new CreateCapture($token));
    }
}

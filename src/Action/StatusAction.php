<?php

namespace RokMohar\PayumValuBundle\Action;

use RokMohar\PayumValuBundle\ValuApi;
use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;

class StatusAction implements ActionInterface
{
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
        return $request instanceof GetStatusInterface && $request->getModel() instanceof ArrayAccess;
    }

    /**
     * @param GetStatusInterface $request
     */
    private function doExecute(GetStatusInterface $request): void
    {
        $model = ArrayObject::ensureArrayObject($request->getModel());
        $status = $model['status'] ?? null;

        switch ($status) {
            case ValuApi::STATUS_CONFIRMED:
                $request->markCaptured();
                break;

            case ValuApi::STATUS_REJECTED:
                $request->markFailed();
                break;

            case ValuApi::STATUS_PENDING:
                $refreshCounter = $model['refreshCounter'] ?? 0;

                if ($refreshCounter > 60) {
                    $request->markExpired();
                } else {
                    $request->markPending();
                }
                break;

            default:
                $request->markPending();
        }
    }
}

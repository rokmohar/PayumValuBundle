<?php

namespace RokMohar\PayumValuBundle\Action;

use RokMohar\PayumValuBundle\ValuApi;
use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\Notify;

class NotifyAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $this->doRequest($request);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request): bool
    {
        return $request instanceof Notify && $request->getModel() instanceof ArrayAccess;
    }

    /**
     * @param Notify $request
     */
    private function doRequest(Notify $request): void
    {
        $requestToken = $request->getToken();
        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (!$model->offsetExists('status')) {
            $model->offsetSet('status', ValuApi::STATUS_PENDING);
        }

        if (!$model->offsetExists('refreshCounter')) {
            $model->offsetSet('refreshCounter', 0);
        }

        $status = $model->offsetGet('status');

        $httpRequest = new GetHttpRequest();
        $this->gateway->execute($httpRequest);

        $responseContent = '<error>1</error>';
        $confirmationIdStatus = $httpRequest->query['ConfirmationIDStatus'] ?? '';

        if ($confirmationIdStatus !== '') {
            $responseContent = sprintf('<status>%s</status>', $status);
        } elseif ($status === ValuApi::STATUS_PENDING) {
            $signature = $httpRequest->query['ConfirmationSignature'] ?? '';
            $errorCode = (int)($httpRequest->query['TARIFFICATIONERROR'] ?? 0);

            $this->gateway->execute($status = new GetHumanStatus($requestToken));

            if ($errorCode === 0) {
                $model->offsetSet('status', ValuApi::STATUS_CONFIRMED);
                $responseContent = '<error>0</error>';
                $status->markCaptured();
            } else {
                $model->offsetSet('status', ValuApi::STATUS_REJECTED);
                $status->markFailed();
            }

            $this->gateway->execute($status);

            $model->offsetSet('confirmationSignature', $signature);
            $model->offsetSet('tarifficationError', $errorCode);
        }

        throw new HttpResponse($responseContent);
    }
}

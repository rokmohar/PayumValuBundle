<?php

namespace RokMohar\PayumValuBundle\Action\Api;

use ArrayAccess;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use RokMohar\PayumValuBundle\Request\Api\CreateCapture;

class CreateCaptureAction extends AbstractAction implements GenericTokenFactoryAwareInterface
{
    use GenericTokenFactoryAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $this->doExecute($request);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request): bool
    {
        return $request instanceof CreateCapture && $request->getModel() instanceof ArrayAccess;
    }

    /**
     * @param CreateCapture $request
     */
    private function doExecute(CreateCapture $request): void
    {
        $requestToken = $request->getToken();
        $notifyToken = $this->tokenFactory->createToken(
            $requestToken->getGatewayName(),
            $requestToken->getDetails(),
            'payum_valu_confirmation'
        );

        $model = ArrayObject::ensureArrayObject($request->getModel());
        $model->offsetSet('confirmation_id', $requestToken->getHash());

        $valuApi = $this->getApi();
        $endpoint = $valuApi->getApiEndpoint();
        $tarifficationId = $valuApi->getTarifficationId();
        $confirmationId = $notifyToken->getHash();

        $url = sprintf('%s?TARIFFICATIONID=%s&ConfirmationID=%s', $endpoint, $tarifficationId, $confirmationId);

        throw new HttpRedirect($url);
    }
}

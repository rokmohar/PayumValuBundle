<?php

namespace RokMohar\PayumValuBundle\Action;

use RokMohar\PayumValuBundle\Request\GetPaymentStatus;
use RokMohar\PayumValuBundle\ValuApi;
use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\RenderTemplate;

class GetPaymentStatusAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /** @var string */
    private $templateName;

    /**
     * @param string $templateName
     */
    public function __construct(string $templateName)
    {
        $this->templateName = $templateName;
    }

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
        return $request instanceof GetPaymentStatus && $request->getModel() instanceof ArrayAccess;
    }

    /**
     * @param GetPaymentStatus $request
     */
    private function doExecute(GetPaymentStatus $request): void
    {
        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (!$model->offsetExists('status')) {
            $model->offsetSet('status', ValuApi::STATUS_PENDING);
        }

        if (!$model->offsetExists('refreshCounter')) {
            $model->offsetSet('refreshCounter', 0);
        }

        $status = $model->offsetGet('status');
        $refreshCounter = $model->offsetGet('refreshCounter');

        if ($status === ValuApi::STATUS_PENDING && $refreshCounter <= ValuApi::EXPIRED_COUNTER) {
            $model->offsetSet('refreshCounter', $refreshCounter + 1);
        }

        $quantity = $model->offsetGet('quantity') ?: ValuApi::DEFAULT_QUANTITY;
        $vatRat = $model->offsetGet('vatRate') ?: ValuApi::DEFAULT_VAT_RATE;

        /** @var PaymentInterface $payment */
        $payment = $request->getFirstModel();

        $httpRequest = new GetHttpRequest();
        $this->gateway->execute($httpRequest);

        $this->gateway->execute($template = new RenderTemplate($this->templateName, [
            'description' => $payment->getDescription(),
            'price' => number_format($payment->getTotalAmount() / 100, 2, '.', ''),
            'currency' => $payment->getCurrencyCode(),
            'quantity' => $quantity,
            'vatRate' => $vatRat,
            'status' => $status,
            'refreshUrl' => $httpRequest->uri,
            'refreshCounter' => $refreshCounter,
        ]));

        throw new HttpResponse($template->getResult());
    }
}

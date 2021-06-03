<?php

namespace RokMohar\PayumValuBundle\Controller;

use RokMohar\PayumValuBundle\Event\CancelAfterEvent;
use RokMohar\PayumValuBundle\Event\ConfirmationBeforeEvent;
use RokMohar\PayumValuBundle\Event\StatusBeforeEvent;
use RokMohar\PayumValuBundle\Request\GetPaymentStatus;
use Exception;
use Payum\Core\Request\Cancel;
use Payum\Core\Request\Notify;
use Payum\Core\Security\TokenInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/payment/valu")
 */
class ValuController extends AbstractController
{
    /**
     * @Route("/status", name="payum_valu_status")
     * @param Request $request
     * @return Response
     */
    public function statusAction(Request $request): Response
    {
        $payum = $this->get('payum');
        $token = $this->getTokenFromRequest($request);

        $beforeEvent = new StatusBeforeEvent($request, $token);
        $dispatcher = $this->get('event_dispatcher');
        $dispatcher->dispatch($beforeEvent, StatusBeforeEvent::NAME);

        if ($beforeEvent->getResponse() !== null) {
            return $beforeEvent->getResponse();
        }

        $gateway = $payum->getGateway($token->getGatewayName());
        $gateway->execute(new GetPaymentStatus($token));

        return new Response('', 200);
    }

    /**
     * @Route("/confirmation", name="payum_valu_confirmation")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function confirmationAction(Request $request): Response
    {
        $confirmationId = $request->query->get('ConfirmationID');
        $request->attributes->set('payum_token', $confirmationId);

        if (empty($confirmationId)) {
            throw $this->createNotFoundException('Confirmation ID is required.');
        }

        $payum = $this->get('payum');
        $token = $payum->getHttpRequestVerifier()->verify($request);

        $beforeEvent = new ConfirmationBeforeEvent($request, $token);
        $dispatcher = $this->get('event_dispatcher');
        $dispatcher->dispatch($beforeEvent, ConfirmationBeforeEvent::NAME);

        if ($beforeEvent->getResponse() !== null) {
            return $beforeEvent->getResponse();
        }

        $gateway = $payum->getGateway($token->getGatewayName());
        $gateway->execute(new Notify($token));

        return new Response('', 204);
    }

    /**
     * @Route("/cancel", name="payum_valu_cancel")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function cancelAction(Request $request): Response
    {
        $payum = $this->get('payum');
        $token = $this->getTokenFromRequest($request);

        $gateway = $payum->getGateway($token->getGatewayName());
        $gateway->execute(new Cancel($token));

        $afterEvent = new CancelAfterEvent($request, $token);
        $dispatcher = $this->get('event_dispatcher');
        $dispatcher->dispatch($afterEvent, CancelAfterEvent::NAME);

        if ($afterEvent->getResponse() !== null) {
            return $afterEvent->getResponse();
        }

        return new Response('', 200);
    }

    /**
     * @param Request $request
     * @return TokenInterface
     */
    private function getTokenFromRequest(Request $request): TokenInterface
    {
        $confirmationId = $request->query->get('ConfirmationID');
        $request->attributes->set('payum_token', $confirmationId);

        if (empty($confirmationId)) {
            throw $this->createNotFoundException('Confirmation ID is required.');
        }

        $payum = $this->get('payum');
        $tokenStorage = $payum->getTokenStorage();

        /** @var TokenInterface|null $token */
        $token = $tokenStorage->find($confirmationId);

        if ($token === null) {
            throw $this->createNotFoundException('Token not found.');
        }

        return $token;
    }
}

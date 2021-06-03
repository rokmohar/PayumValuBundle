<?php

namespace RokMohar\PayumValuBundle\Event;

use Payum\Core\Security\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractEvent extends Event
{
    /** @var Request */
    private $request;

    /** @var TokenInterface */
    private $token;

    /** @var Response|null */
    private $response;

    /**
     * @param Request $request
     * @param TokenInterface $token
     */
    public function __construct(Request $request, TokenInterface $token)
    {
        $this->request = $request;
        $this->token = $token;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @return TokenInterface
     */
    public function getToken(): TokenInterface
    {
        return $this->token;
    }

    /**
     * @return Response|null
     */
    public function getResponse(): ?Response
    {
        return $this->response;
    }

    /**
     * @param Response|null $response
     */
    public function setResponse(?Response $response): void
    {
        $this->response = $response;
    }
}

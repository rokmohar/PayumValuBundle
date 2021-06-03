<?php

namespace RokMohar\PayumValuBundle\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Core\Security\TokenInterface;

class CreateCapture extends Generic
{
    /**
     * @param TokenInterface $token
     */
    public function __construct(TokenInterface $token)
    {
        parent::__construct($token);
    }

    /**
     * @return TokenInterface
     */
    public function getToken(): TokenInterface
    {
        return $this->token;
    }
}

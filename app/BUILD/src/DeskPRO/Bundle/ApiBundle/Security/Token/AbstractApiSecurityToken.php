<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\ApiBundle\Security\Token;

use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;

abstract class AbstractApiSecurityToken extends PreAuthenticatedToken
{
    abstract public function getName();
}

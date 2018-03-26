<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\ApiBundle\Security\Token;

class ApiKeySecurityToken extends AbstractApiSecurityToken
{
    public function getName()
    {
        return 'api_key';
    }
}

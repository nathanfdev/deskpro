<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\ApiBundle\Security\Token;

class ApiTokenSecurityToken extends AbstractApiSecurityToken
{
    public function getName()
    {
        return 'api_token';
    }
}

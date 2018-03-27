<?php

namespace DeskPRO\Bundle\ApiBundle\Security\Token;

/**
 * Class PortalSessionSecurityToken.
 */
class PortalSessionSecurityToken extends AbstractApiSecurityToken
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'portal_session';
    }
}

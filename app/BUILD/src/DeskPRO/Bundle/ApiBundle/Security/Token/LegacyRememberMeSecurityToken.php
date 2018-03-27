<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\ApiBundle\Security\Token;

class LegacyRememberMeSecurityToken extends AbstractApiSecurityToken
{
    protected $app_id;

    public function getName()
    {
        return 'agent_session';
    }

    /**
     * @return mixed
     */
    public function getAppId()
    {
        return $this->app_id;
    }

    /**
     * @param mixed $app_id
     */
    public function setAppId($app_id)
    {
        $this->app_id = $app_id;
    }
}

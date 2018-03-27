<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Auth;

class AuthSettings
{
    /**
     * @var AuthInterfaceSettings
     */
    private $userInterfaceSettings;

    /**
     * @var AuthInterfaceSettings
     */
    private $agentInterfaceSettings;

    public function __construct(
        AuthInterfaceSettings $userInterfaceSettings,
        AuthInterfaceSettings $agentInterfaceSettings
    ) {
        $this->userInterfaceSettings  = $userInterfaceSettings;
        $this->agentInterfaceSettings = $agentInterfaceSettings;
    }

    /**
     * @return AuthInterfaceSettings
     */
    public function getAgentInterfaceSettings()
    {
        return $this->agentInterfaceSettings;
    }

    /**
     * @return AuthInterfaceSettings
     */
    public function getUserInterfaceSettings()
    {
        return $this->userInterfaceSettings;
    }
}

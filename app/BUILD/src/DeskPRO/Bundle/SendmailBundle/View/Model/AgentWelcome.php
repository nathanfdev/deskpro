<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use JMS\Serializer\Annotation as JMS;

class AgentWelcome extends EmailBaseType
{
    /**
     * Link to agent interface.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $loginLink;

    /**
     * Agent password (if it has been set).
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $agentPassword;

    protected $templateFile = 'emails_agent:agent_welcome.html.twig';

    /**
     * AgentWelcome constructor.
     *
     * @param string $agentPassword
     * @param $loginLink
     */
    public function __construct($agentPassword, $loginLink)
    {
        $this->loginLink     = $loginLink;
        $this->agentPassword = $agentPassword;
    }
}

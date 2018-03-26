<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class AgentWelcomeUsersource extends EmailBaseType
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

    protected $templateFile = 'emails_agent:agent_welcome_usersource.html.twig';

    /**
     * AgentWelcome constructor.
     *
     * @param RouterInterface $router
     * @param string          $agentPassword
     */
    public function __construct(RouterInterface $router, $agentPassword)
    {
        $this->loginLink     = $router->generate('agent', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $this->agentPassword = $agentPassword;
    }
}

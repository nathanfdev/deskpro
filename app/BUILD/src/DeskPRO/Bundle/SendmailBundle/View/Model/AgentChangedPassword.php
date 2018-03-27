<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use JMS\Serializer\Annotation as JMS;

class AgentChangedPassword extends EmailBaseType
{
    /**
     * Link to user profile.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $userLink;

    /**
     * New password set by agent.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $newPassword;

    protected $templateFile = 'emails_user:agent_changed_password.html.twig';

    public function __construct($userLink, $newPassword)
    {
        $this->newPassword = $newPassword;

        $this->userLink = $userLink;
    }
}

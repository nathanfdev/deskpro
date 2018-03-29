<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use JMS\Serializer\Annotation as JMS;

class RegisterWelcomeByAgent extends UserEmailBaseType
{
    /**
     * New password set by agent.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $newPassword;

    protected $templateFile = 'emails_user:register_welcome_by_agent.html.twig';

    public function __construct($portalHome, $newPassword)
    {
        parent::__construct($portalHome);

        $this->newPassword = $newPassword;
    }
}

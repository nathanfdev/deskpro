<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person;
use JMS\Serializer\Annotation as JMS;

class AgentPasswordResetAlert extends EmailBaseType
{
    /**
     * Person that perform the password reset.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person")
     *
     * @var Person
     */
    protected $performer;

    /**
     * New password.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $newPassword;

    /**
     * Link to agent interface.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $loginLink;

    protected $templateFile = 'emails_agent:password_reset_alert.html.twig';

    public function __construct(Person $performer, $newPassword, $loginLink)
    {
        $this->performer   = $performer;
        $this->newPassword = $newPassword;
        $this->loginLink   = $loginLink;
    }
}

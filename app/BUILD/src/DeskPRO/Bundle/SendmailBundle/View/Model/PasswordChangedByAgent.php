<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use JMS\Serializer\Annotation as JMS;

class PasswordChangedByAgent extends EmailBaseType
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

    /**
     * @var string
     */
    protected $templateFile = 'emails_user:password_changed_by_agent.html.twig';

    public function __construct($userLink, $newPassword)
    {
        $this->newPassword = $newPassword;

        $this->userLink = $userLink;
    }
}

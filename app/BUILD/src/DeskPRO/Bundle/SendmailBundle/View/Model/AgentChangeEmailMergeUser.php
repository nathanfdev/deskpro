<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

class AgentChangeEmailMergeUser extends EmailBaseType
{
    /**
     * The previous email address.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $oldEmail;

    /**
     * The new email address.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $newEmail;

    protected $templateFile = 'emails_agent:agent_changeemail_mergeuser.html.twig';

    public function __construct($oldEmail, $newEmail)
    {
        $this->oldEmail = $oldEmail;
        $this->newEmail = $newEmail;
    }
}

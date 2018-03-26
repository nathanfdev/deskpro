<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use JMS\Serializer\Annotation as JMS;

class AgentErrorInvalidForward extends EmailBaseType
{
    /**
     * Error code.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $error;

    protected $templateFile = 'emails_agent:error_invalid_forward.html.twig';

    public function __construct($error)
    {
        $this->error = $error;
    }
}

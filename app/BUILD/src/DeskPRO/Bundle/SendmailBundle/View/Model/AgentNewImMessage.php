<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person;
use JMS\Serializer\Annotation as JMS;

class AgentNewImMessage extends EmailBaseType
{
    use EventCodeEmailBaseType;

    /**
     * The chat message.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage")
     *
     * @var AgentChatMessage
     */
    protected $chatMessage;

    /**
     * The author of the message.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person")
     *
     * @var Person
     */
    protected $author;

    protected $templateFile = 'emails_agent:new_agent_im_message.html.twig';

    public function __construct(AgentChatMessage $chatMessage, $author)
    {
        $this->chatMessage = $chatMessage;
        $this->author      = $author;
    }

    /**
     * @return string
     */
    public function getEventCodeType()
    {
        return 'im';
    }
}

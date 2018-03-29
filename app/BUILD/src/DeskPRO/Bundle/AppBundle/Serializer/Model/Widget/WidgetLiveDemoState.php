<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Widget;

use Application\DeskPRO\Entity\ChatConversation;
use JMS\Serializer\Annotation as JMS;

/**
 * Class WidgetLiveDemoState.
 */
class WidgetLiveDemoState
{
    /**
     * @var WidgetPeopleDemoState
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Widget\WidgetPeopleDemoState")
     */
    private $people;

    /**
     * @var WidgetChatDemoState
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Widget\WidgetChatDemoState")
     */
    private $chat;

    /**
     * @var array
     *
     * @JMS\Type("array<Application\DeskPRO\Entity\Department>")
     */
    private $departments;

    /**
     * @var array
     *
     * @JMS\Type("array<DeskPRO\Bundle\AppBundle\Entity\Currency>")
     */
    private $currencies;

    /**
     * Constructor.
     *
     * @param array            $agents
     * @param array            $users
     * @param ChatConversation $chat
     * @param array            $departments
     * @param array            $currencies
     */
    public function __construct(array $agents, array $users, ChatConversation $chat, array $departments, array $currencies)
    {
        $this->people      = new WidgetPeopleDemoState($agents, $users);
        $this->chat        = new WidgetChatDemoState($chat);
        $this->departments = $departments;
        $this->currencies  = $currencies;
    }
}

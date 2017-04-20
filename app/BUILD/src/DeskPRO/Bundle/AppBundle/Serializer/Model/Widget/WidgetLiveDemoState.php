<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
     * Constructor.
     *
     * @param array            $agents
     * @param array            $users
     * @param ChatConversation $chat
     */
    public function __construct(array $agents, array $users, ChatConversation $chat, array $departments)
    {
        $this->people      = new WidgetPeopleDemoState($agents, $users);
        $this->chat        = new WidgetChatDemoState($chat);
        $this->departments = $departments;
    }
}

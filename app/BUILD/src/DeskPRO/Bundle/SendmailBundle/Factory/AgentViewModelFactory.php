<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\SendmailBundle\Factory;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\ObjectRouter\ObjectRouter;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentChangedPassword;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentErrorInvalidForward;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentErrorMarkerMissing;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentErrorUnknownFrom;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentLoginAlert;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentNewChatMessage;

class AgentViewModelFactory
{
    /**
     * @var ObjectRouter
     */
    private $objectRouter;

    /**
     * Constructor.
     *
     * @param ObjectRouter $objectRouter
     */
    public function __construct(ObjectRouter $objectRouter)
    {
        $this->objectRouter = $objectRouter;
    }

    /**
     * @return AgentChangedPassword
     */
    public function createAgentChangedPasswordModel()
    {
        return new AgentChangedPassword();
    }

    /**
     * @return AgentErrorInvalidForward
     */
    public function createAgentErrorInvalidForwardModel()
    {
        return new AgentErrorInvalidForward();
    }

    /**
     * @return AgentErrorMarkerMissing
     */
    public function createAgentErrorMarkerMissingModel()
    {
        return new AgentErrorMarkerMissing();
    }

    /**
     * @return AgentErrorUnknownFrom
     */
    public function createAgentErrorUnknownFromModel()
    {
        return new AgentErrorUnknownFrom();
    }

    /**
     * @return AgentLoginAlert
     */
    public function createAgentLoginAlertModel()
    {
        return new AgentLoginAlert();
    }

    /**
     * @param Ticket $ticket
     *
     * @return AgentNewChatMessage
     */
    public function createAgentNewChatMessageModel(
        $ticket
    ) {
        return new AgentNewChatMessage($this->objectRouter, $ticket);
    }
}

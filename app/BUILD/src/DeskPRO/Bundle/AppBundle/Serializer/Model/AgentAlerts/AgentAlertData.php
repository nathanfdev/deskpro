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

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\AgentAlerts;

use JMS\Serializer\Annotation as JMS;

/**
 * Class AgentAlertData.
 */
class AgentAlertData
{
    /**
     * Ticket identity.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $ticket;

    /**
     * Additional data about notification.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\AgentAlerts\NotifyData")
     *
     * @var NotifyData
     */
    private $notification;

    /**
     * Who performed this alert. Identity.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $performer;

    /**
     * AgentAlertData constructor.
     *
     * @param int        $ticket
     * @param NotifyData $notification
     * @param int        $performer
     */
    public function __construct($ticket, NotifyData $notification, $performer)
    {
        $this->ticket       = $ticket;
        $this->notification = $notification;
        $this->performer    = $performer;
    }
}

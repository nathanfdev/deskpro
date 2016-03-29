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

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\AgentAlert;

use Application\DeskPRO\Entity\AgentAlert as AgentAlertEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * Class AgentAlert.
 */
class AgentAlert
{
    /**
     * Unique identity of alert.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $uuid;

    /**
     * Alert type.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $type;

    /**
     * Alert data.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\AgentAlert\AgentAlertData")
     *
     * @var AgentAlertData
     */
    private $data;

    /**
     * When this alert was created.
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $dateCreated;

    /**
     * Is it dismissed?
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $isDismissed;

    /**
     * AgentAlert constructor.
     *
     * @param AgentAlertEntity $alert
     * @param string           $type
     * @param AgentAlertData   $data
     */
    public function __construct(AgentAlertEntity $alert, $type, AgentAlertData $data)
    {
        $this->uuid        = $alert->getId();
        $this->dateCreated = $alert->getDateCreated();
        $this->isDismissed = $alert->isDismissed();
        $this->type        = $type;
        $this->data        = $data;
    }
}

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

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets;

use JMS\Serializer\Annotation as JMS;

/**
 * Class TicketFieldsInfoSettings.
 */
class TicketFieldsInfoSettings
{
    /**
     * @var TicketFieldInfoSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\TicketFieldInfoSettings")
     */
    private $product;

    /**
     * @var TicketFieldInfoSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\TicketFieldInfoSettings")
     */
    private $category;

    /**
     * @var TicketFieldInfoSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\TicketFieldInfoSettings")
     */
    private $workflow;

    /**
     * @var TicketFieldInfoSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\TicketFieldInfoSettings")
     */
    private $priority;

    /**
     * @var TicketCustomFieldInfoSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\TicketCustomFieldInfoSettings")
     */
    private $custom;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->product  = new TicketFieldInfoSettings();
        $this->category = new TicketFieldInfoSettings();
        $this->workflow = new TicketFieldInfoSettings();
        $this->priority = new TicketFieldInfoSettings();
        $this->custom   = new TicketCustomFieldInfoSettings();
    }

    /**
     * @return TicketFieldInfoSettings
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return TicketFieldInfoSettings
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return TicketFieldInfoSettings
     */
    public function getWorkflow()
    {
        return $this->workflow;
    }

    /**
     * @return TicketFieldInfoSettings
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @return TicketCustomFieldInfoSettings
     */
    public function getCustom()
    {
        return $this->custom;
    }
}

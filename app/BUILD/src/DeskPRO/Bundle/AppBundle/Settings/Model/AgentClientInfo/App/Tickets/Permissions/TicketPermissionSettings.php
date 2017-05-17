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

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\Permissions;

use JMS\Serializer\Annotation as JMS;

/**
 * Class TicketPermissionSettings.
 */
class TicketPermissionSettings
{
    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $create;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $replyMass;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $modifySetArchived;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $createLabels;

    /**
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\Permissions\TicketModifyPermissionSettings")
     *
     * @var TicketModifyPermissionSettings
     */
    private $modify;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->modify = new TicketModifyPermissionSettings();
    }

    /**
     * @return bool
     */
    public function isCreate()
    {
        return $this->create;
    }

    /**
     * @param bool $create
     *
     * @return $this
     */
    public function setCreate($create)
    {
        $this->create = $create;

        return $this;
    }

    /**
     * @return bool
     */
    public function isReplyMass()
    {
        return $this->replyMass;
    }

    /**
     * @param bool $replyMass
     *
     * @return $this
     */
    public function setReplyMass($replyMass)
    {
        $this->replyMass = $replyMass;

        return $this;
    }

    /**
     * @return bool
     */
    public function isModifySetArchived()
    {
        return $this->modifySetArchived;
    }

    /**
     * @param bool $modifySetArchived
     *
     * @return $this
     */
    public function setModifySetArchived($modifySetArchived)
    {
        $this->modifySetArchived = $modifySetArchived;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCreateLabels()
    {
        return $this->createLabels;
    }

    /**
     * @param bool $createLabels
     *
     * @return $this
     */
    public function setCreateLabels($createLabels)
    {
        $this->createLabels = $createLabels;

        return $this;
    }

    /**
     * @return TicketModifyPermissionSettings
     */
    public function getModify()
    {
        return $this->modify;
    }
}

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

namespace DeskPRO\Bundle\AppBundle\Serializer\Model;

use Application\DeskPRO\Entity\AgentTeam as AgentTeamEntity;
use DeskPRO\Bundle\AppBundle\Content\Avatar;

/**
 * Class AgentTeam.
 */
class AgentTeam
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var
     */
    protected $avatar;

    /**
     * AgentTeam constructor.
     *
     * @param AgentTeamEntity $agent_team
     */
    public function __construct(AgentTeamEntity $agent_team)
    {
        $this->id   = $agent_team->getId();
        $this->name = $agent_team->getName();
    }

    /**
     * @param Avatar $avatar
     *
     * @return $this
     */
    public function setAvatar(Avatar $avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }
}

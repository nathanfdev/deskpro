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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\DataService\UserGroups;

use Application\DeskPRO\Entity\Usergroup;
use Application\DeskPRO\EntityRepository\Usergroup as UsergroupRepo;
use DeskPRO\Bundle\AppBundle\DataService\AbstractDataService;

/**
 * Class UserGroupsDataService.
 */
class UserGroupsDataService extends AbstractDataService
{
    /**
     * @param array $args
     * @param null  $agents_only
     * @param null  $enabled
     *
     * @return array
     */
    protected function criteriaArray(array $args = [], $agents_only = null, $enabled = null)
    {
        if (!is_null($agents_only)) {
            $args['is_agent_group'] = (bool) $agents_only;
        }

        if (!is_null($enabled)) {
            $args['is_enabled'] = (bool) $enabled;
        }

        return $args;
    }

    /**
     * @param bool $agents_only
     * @param bool $enabled
     *
     * @return Usergroup[]
     */
    public function loadAll($agents_only = null, $enabled = null)
    {
        return $this->getRepo()->findBy($this->criteriaArray([], $agents_only, $enabled));
    }

    /**
     * @param int  $id
     * @param bool $agents_only
     * @param bool $enabled
     *
     * @return null|Usergroup
     */
    public function loadOne($id, $agents_only = null, $enabled = null)
    {
        return $this->getRepo()->findOneBy(
            $this->criteriaArray(['id' => $id], $agents_only, $enabled)
        );
    }

    /**
     * @return Usergroup[]
     */
    public function loadAgentGroups()
    {
        return $this->loadAll(true);
    }

    /**
     * @return Usergroup[]
     */
    public function loadUserGroups()
    {
        return $this->loadAll(false);
    }

    /**
     * @return Usergroup[]
     */
    public function loadAllEnabled()
    {
        return $this->loadAll(null, true);
    }

    /**
     * @return Usergroup[]
     */
    public function loadAgentGroupsEnabled()
    {
        return $this->loadAll(true, true);
    }

    /**
     * @return Usergroup[]
     */
    public function loadUserGroupsEnabled()
    {
        return $this->loadAll(false, true);
    }

    /**
     * @param int $id
     *
     * @return null|Usergroup
     */
    public function loadSingle($id)
    {
        return $this->loadOne($id);
    }

    /**
     * @param $id
     *
     * @return null|Usergroup
     */
    public function loadSingleEnabled($id)
    {
        return $this->loadOne($id, null, true);
    }

    /**
     * @param $id
     *
     * @return null|Usergroup
     */
    public function loadSingleAgentGroupEnabled($id)
    {
        return $this->loadOne($id, true, true);
    }

    /**
     * @param $id
     *
     * @return null|Usergroup
     */
    public function loadSingleUserGroupEnabled($id)
    {
        return $this->loadOne($id, false, true);
    }

    /**
     * @return UsergroupRepo
     */
    public function getRepo()
    {
        return $this->em->getRepository('DeskPRO:Usergroup');
    }
}

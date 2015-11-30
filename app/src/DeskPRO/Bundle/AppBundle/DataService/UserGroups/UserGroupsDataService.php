<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\EntityRepository\Person as PersonRepo;
use DeskPRO\Bundle\AppBundle\CountBadge\Count;
use DeskPRO\Bundle\AppBundle\DataService\AbstractDataService;

class UserGroupsDataService extends AbstractDataService
{
    /**
     * @return Count
     */
    public function countPeopleInUserGroups()
    {
        $qb = $this->em->createQueryBuilder();

        $qb->select('count(p) as value, ug.id as group_name')
            ->from('DeskPRO:Person', 'p')
            ->join('p.usergroups', 'ug')
            ->andWhere('ug.is_agent_group = false')
            ->andWhere('ug.is_enabled = true')
            ->andWhere('p.is_deleted = false')
            ->groupBy('group_name')
        ;

        $result = $qb->getQuery()->getArrayResult();

        $count = Count::fromGroupedBy('user_group');
        foreach ($result as $group) {
            $count->add($group['value']);
            $count->addNested($group['value'], $group['group_name'], 'user_group');
        }

        return $count;
    }

    protected function criteriaArray(array $args = [], $agents_only = null, $enabled = null)
    {
        if ($agents_only === true) {
            $args['is_agent_group'] = true;
        } elseif ($agents_only === false) {
            $args['is_agent_group'] = false;
        }
        if ($enabled === true) {
            $args['is_enabled'] = true;
        } elseif ($enabled === false) {
            $args['is_enabled'] = false;
        }

        return $args;
    }

    /**
     * @param mixed $person right now only ID is useful
     *
     * @return Person|null
     */
    public function loadAll($agents_only = null, $enabled = null)
    {
        return $this->getRepo()->findBy($this->criteriaArray([], $agents_only, $enabled));
    }

    public function loadOne($id, $agents_only = null, $enabled = null)
    {
        return $this->getRepo()->findOneBy(
            $this->criteriaArray(['id' => $id], $agents_only, $enabled)
        );
    }

    public function loadAgentGroups()
    {
        return $this->loadAll(true);
    }

    public function loadUserGroups()
    {
        return $this->loadAll(false);
    }

    public function loadAllEnabled()
    {
        return $this->loadAll(null, true);
    }

    public function loadAgentGroupsEnabled()
    {
        return $this->loadAll(true, true);
    }

    public function loadUserGroupsEnabled()
    {
        return $this->loadAll(false, true);
    }

    /**
     * @param $email
     *
     * @return Person|null
     */
    public function loadSingle($id)
    {
        // using caution and not caching most PersonDataService methods
        return $this->loadOne($id);
    }

    public function loadSingleEnabled($id)
    {
        return $this->loadOne($id, null, true);
    }

    public function loadSingleAgentGroupEnabled($id)
    {
        return $this->loadOne($id, true, true);
    }

    public function loadSingleUserGroupEnabled($id)
    {
        return $this->loadOne($id, false, true);
    }

    /**
     * @return PersonRepo
     */
    public function getRepo()
    {
        return $this->em->getRepository('DeskPRO:Usergroup');
    }
}

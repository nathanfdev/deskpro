<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\AuthBundle\Permissions\Portal;

use Application\AppBundle\Helper\ArbitraryHasher;
use Application\DeskPRO\Cache\Adapter\SimpleArrayCache;
use Application\DeskPRO\Cache\ConvenientCache;
use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\ORM\EntityManager;

class PortalUsergroupDecider
{
    /**
     * @var ArbitraryHasher
     */
    protected $hash_generator;

    /**
     * @var ConvenientCache
     */
    protected $cache;

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    private $conn;

    /**
     * @var \Application\DeskPRO\ORM\EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->conn = $em->getConnection();
        $this->em = $em;
    }

    /**
     * Responsible for deciding the exact set of usergroups to use for the given person
     *
     * @param Person $person
     * @return array
     */
    public function getUsergroupIdsForPerson(Person $person)
    {
        $that = $this;
        $conn = $this->conn;
        $em = $this->em;

        return $this->generateAndCache(
            array(
                'getUsergroupIdsForPerson',
                $person
            ),
            function () use ($that, $conn, $em, $person) {
                $ids = $conn->fetchAllCol(
                    '
            SELECT person2usergroups.usergroup_id
            FROM person2usergroups
            LEFT JOIN usergroups ON usergroups.id = person2usergroups.usergroup_id
            WHERE person2usergroups.person_id = ? AND usergroups.is_enabled = 1
            ',
                    array($person['id'])
                );

                if ($everyoneGroups = $that->getGroupsThatApplyToEveryone()) {
                    $ids = array_merge($ids, $everyoneGroups);
                }

                // if a user is logged in and agent confirmed, they also get the "regsitered" perm.
                $registeredGroup = $em->getRepository('DeskPRO:Usergroup')->findOneBy(array('sys_name' => 'registered'));
                if ($registeredGroup && $registeredGroup->is_enabled && $person->getId() && $person->is_agent_confirmed) {
                    $ids[] = $registeredGroup->id;
                }


                if ($person->organization) {
                    if ($org_usergroup_ids = $that->getOrganizationUsergroups($person->organization['id'])) {
                        $ids = array_merge($ids, $org_usergroup_ids);
                    }
                }

                return array_unique($ids);
            }
        );
    }

    /**
     * Responsible for delivering a set of group IDs that are to be used for guests
     */
    public function getUsergroupIdsForGuest()
    {
        return $this->getGroupsThatApplyToEveryone();
    }

    /**
     * Usergroup IDs for an organization (via ID)
     *
     * @param $organizationId
     * @return array
     */
    public function getOrganizationUsergroups($organizationId)
    {
        $conn = $this->conn;

        return $this->generateAndCache(
            array(
                'getOrganizationUsergroups',
                $organizationId
            ),
            function () use ($conn, $organizationId) {
                return $conn->fetchAllCol(
                    '
            SELECT organization2usergroups.usergroup_id
            FROM organization2usergroups
            JOIN usergroups ON usergroups.id = organization2usergroups.usergroup_id
            WHERE organization2usergroups.organization_id = ? AND usergroups.is_enabled = 1
            ',
                    array($organizationId)
                );
            }
        );
    }

    /**
     * The usergroup IDs that EVERYONE has by default (including guests)
     *
     * @return array
     */
    public function getGroupsThatApplyToEveryone()
    {
        $em = $this->em;

        return $this->generateAndCache(
            array(
                'getGroupsThatApplyToEveryone'
            ),
            function() use ($em) {
                // everyone gets the everyone group if it exists and is enabled
                $everyoneGroup = $em->getRepository('DeskPRO:Usergroup')->findOneBy(array('sys_name' => 'everyone'));
                if ($everyoneGroup && $everyoneGroup->is_enabled) {
                    return array($everyoneGroup->id);
                }

                return array();
            }
        );
    }

    /**
     * @param mixed $params   the "ArbitraryHasher" input to create cache key for this callable
     * @param mixed $callable doesn't need to be a callable, can be any default value, but usually is a callable
     * @return mixed|null
     */
    protected function generateAndCache($params, $callable)
    {
        return $this->getCache()->get($this->generateHash($params), $callable);
    }

    /**
     * @return ConvenientCache
     */
    protected function getCache()
    {
        if (null === $this->cache) {
            $this->cache = new ConvenientCache(new SimpleArrayCache());
        }

        return $this->cache;
    }

    /**
     * @param mixed $input
     * @return string
     */
    protected function generateHash($input)
    {
        if (null === $this->hash_generator) {
            $this->hash_generator = new ArbitraryHasher();
        }

        return $this->hash_generator->generateHash($input);
    }
}
 
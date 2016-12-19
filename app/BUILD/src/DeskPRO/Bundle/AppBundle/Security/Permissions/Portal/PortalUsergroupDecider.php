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

namespace DeskPRO\Bundle\AppBundle\Security\Permissions\Portal;

use Application\DeskPRO\Cache\Adapter\SimpleArrayCache;
use Application\DeskPRO\Cache\ConvenientCache;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Helper\ArbitraryHasher;
use Doctrine\ORM\EntityManager;

/**
 * Given a user (or guest) find out what usergroups they can access.
 *
 * This is needed because we don't store all usergroups to a person. Some of them are autoamtically determined.
 * For example "Everyone" is applied to everyone, but we don't store that.
 *
 * So, use this service to get an accurage list of usergroups for a person.
 */
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
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->conn = $em->getConnection();
        $this->em   = $em;
    }

    /**
     * Responsible for deciding the exact set of usergroups to use for the given person.
     *
     * @param Person $person
     *
     * @return array
     */
    public function getUsergroupIdsForPerson(Person $person)
    {
        return $this->generateAndCache(
            [
                'getUsergroupIdsForPerson',
                $person,
            ],
            function () use ($person) {
                $ids = $this->conn->fetchAllCol(
                    '
            SELECT person2usergroups.usergroup_id
            FROM person2usergroups
            LEFT JOIN usergroups ON usergroups.id = person2usergroups.usergroup_id
            WHERE person2usergroups.person_id = ? AND usergroups.is_enabled = 1 AND usergroups.is_agent_group = 0
            ',
                    [$person['id']]
                );

                if ($everyoneGroups = $this->getGroupsThatApplyToEveryone()) {
                    $ids = array_merge($ids, $everyoneGroups);
                }

                // if a user is logged in and agent confirmed, they also get the "regsitered" perm.
                $registeredGroup = $this->em->getRepository('DeskPRO:Usergroup')->findOneBy(['sys_name' => 'registered']);
                if ($registeredGroup && $registeredGroup->is_enabled && $person->getId()) {
                    $ids[] = $registeredGroup->id;
                }

                if ($person->getOrganization()) {
                    if ($org_usergroup_ids = $this->getOrganizationUsergroups($person->getOrganization()->getId())) {
                        $ids = array_merge($ids, $org_usergroup_ids);
                    }
                }

                return array_unique($ids);
            }
        );
    }

    /**
     * Responsible for delivering a set of group IDs that are to be used for guests.
     */
    public function getUsergroupIdsForGuest()
    {
        return $this->getGroupsThatApplyToEveryone();
    }

    /**
     * Usergroup IDs for an organization (via ID).
     *
     * @param $organizationId
     *
     * @return array
     */
    public function getOrganizationUsergroups($organizationId)
    {
        return $this->generateAndCache(
            [
                'getOrganizationUsergroups',
                $organizationId,
            ],
            function () use ($organizationId) {
                return $this->conn->fetchAllCol(
                    '
            SELECT organization2usergroups.usergroup_id
            FROM organization2usergroups
            JOIN usergroups ON usergroups.id = organization2usergroups.usergroup_id
            WHERE organization2usergroups.organization_id = ? AND usergroups.is_enabled = 1
            ',
                    [$organizationId]
                );
            }
        );
    }

    /**
     * The usergroup IDs that EVERYONE has by default (including guests).
     *
     * @return array
     */
    public function getGroupsThatApplyToEveryone()
    {
        return $this->generateAndCache(
            [
                'getGroupsThatApplyToEveryone',
            ],
            function () {
                // everyone gets the everyone group if it exists and is enabled
                $everyoneGroup = $this->em->getRepository('DeskPRO:Usergroup')->findOneBy(['sys_name' => 'everyone']);
                if ($everyoneGroup && $everyoneGroup->is_enabled) {
                    return [$everyoneGroup->id];
                }

                return [];
            }
        );
    }

    /**
     * @param mixed $params   the "ArbitraryHasher" input to create cache key for this callable
     * @param mixed $callable doesn't need to be a callable, can be any default value, but usually is a callable
     *
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
     *
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

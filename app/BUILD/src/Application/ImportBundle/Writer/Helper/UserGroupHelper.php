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

namespace Application\ImportBundle\Writer\Helper;

use Application\DeskPRO\Entity;
use Application\DeskPRO\Entity\Usergroup;
use Application\ImportBundle\Model;
use Application\ImportBundle\Model\UsergroupAwareModelInterface;
use Application\ImportBundle\Writer\EntityPersister;
use Application\ImportBundle\Writer\Mapper\UserGroupMapper;
use Orb\Util\Strings;
use Psr\Log\LoggerInterface;

/**
 * Class UserGroupHelper.
 */
class UserGroupHelper
{
    /**
     * @var UserGroupMapper
     */
    private $mapper;

    /**
     * @var EntityPersister
     */
    private $persister;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param UserGroupMapper $mapper
     * @param EntityPersister $persister
     * @param LoggerInterface $logger
     */
    public function __construct(UserGroupMapper $mapper, EntityPersister $persister, LoggerInterface $logger)
    {
        $this->mapper    = $mapper;
        $this->persister = $persister;
        $this->logger    = $logger;
    }

    /**
     * @param UsergroupAwareModelInterface $model
     * @param mixed                        $entity
     */
    public function updateUserGroups(UsergroupAwareModelInterface $model, $entity)
    {
        // add new user groups
        foreach ($model->getUserGroups() as $groupName) {
            $entity->addUsergroup($this->findOrCreateUserGroup($groupName));
        }

        // remove deleted user groups
        /** @var Usergroup $usergroup */
        foreach ($entity->getUsergroups() as $usergroup) {
            if ($usergroup->isAgentGroup()) {
                continue;
            }
            if (!in_array($usergroup->getSysName(), $model->getUserGroups()) && !in_array($usergroup->getTitle(), $model->getUserGroups())) {
                $entity->getUsergroups()->removeElement($usergroup);
            }
        }
    }

    /**
     * @param Model\Person  $model
     * @param Entity\Person $entity
     */
    public function updateAgentGroups(Model\Person $model, Entity\Person $entity)
    {
        // add new agent groups
        foreach ($model->getAgentGroups() as $groupName) {
            $entity->addUsergroup($this->findOrCreateUserGroup($groupName, true));
        }

        // remove deleted agent groups
        /** @var Usergroup $usergroup */
        foreach ($entity->getUsergroups() as $usergroup) {
            if (!$usergroup->isAgentGroup()) {
                continue;
            }
            if (!in_array($usergroup->getSysName(), $model->getAgentGroups()) && !in_array($usergroup->getTitle(), $model->getAgentGroups())) {
                $entity->getUsergroups()->removeElement($usergroup);
            }
        }
    }

    /**
     * Returns an user group by sys name.
     *
     * @param string $name
     * @param bool   $isAgent
     *
     * @throws \Exception
     *
     * @return Usergroup
     */
    protected function findOrCreateUserGroup($name, $isAgent = false)
    {
        // try to fetch usergroup by sys name
        $sysName = Strings::slugifyTitleToUnderscore($name);
        $entity  = $this->mapper->findOneBySysName($name, false);
        if ($entity) {
            $this->logger->debug(sprintf(
                'Found existing user group `%d` with title `%s` by sys name `%s`',
                $entity->getId(), $entity->getTitle(), $sysName
            ));

            return $entity;
        }

        // try to fetch usergroup by title
        $entity = $this->mapper->findOneByTitle($name, false);
        if ($entity) {
            $this->logger->debug(sprintf(
                'Found existing user group `%d` by title `%s`',
                $entity->getId(), $entity->getTitle()
            ));

            return $entity;
        }

        // if usergroup does not exist then create a new one
        $this->logger->debug(sprintf('No user group with name `%s` found, create a new one', $name));

        $entity = new Usergroup();
        $entity->setTitle($name);
        $entity->setSysName($sysName);
        $entity->setIsAgentGroup($isAgent);

        $this->persister->persistAndFlush($entity);

        return $entity;
    }
}

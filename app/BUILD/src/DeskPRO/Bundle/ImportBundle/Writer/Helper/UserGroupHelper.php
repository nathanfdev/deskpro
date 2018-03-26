<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Helper;

use Application\DeskPRO\Entity;
use Application\DeskPRO\Entity\Usergroup;
use DeskPRO\Bundle\ImportBundle\Model;
use DeskPRO\Bundle\ImportBundle\Model\UsergroupAwareModelInterface;
use DeskPRO\Bundle\ImportBundle\Writer\EntityPersister;
use DeskPRO\Bundle\ImportBundle\Writer\Mapper\UserGroupMapper;
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
    public function updateUserGroupsByModel(UsergroupAwareModelInterface $model, $entity)
    {
        $this->updateUserGroups($entity, $model->getUserGroups());
    }

    /**
     * @param array $modelUserGroups
     * @param mixed $entity
     */
    public function updateUserGroups($entity, array $modelUserGroups = [])
    {
        $reflection = new \ReflectionClass($entity);
        if (!$reflection->hasProperty('usergroups')) {
            return;
        }

        if (!$modelUserGroups) {
            $modelUserGroups = [Usergroup::EVERYONE];
        }

        // add new user groups
        foreach ($modelUserGroups as $groupName) {
            $entity->addUsergroup($this->findOrCreateUserGroup($groupName));
        }

        // remove deleted user groups
        /** @var Usergroup $usergroup */
        foreach ($entity->getUsergroups() as $usergroup) {
            if ($usergroup->isAgentGroup()) {
                continue;
            }
            if (!in_array($usergroup->getSysName(), $modelUserGroups) && !in_array($usergroup->getTitle(), $modelUserGroups)) {
                $entity->getUsergroups()->removeElement($usergroup);
            }
        }
    }

    /**
     * @param Model\Person  $model
     * @param Entity\Person $entity
     */
    public function updateAgentGroupsByModel(Model\Person $model, Entity\Person $entity)
    {
        $reflection = new \ReflectionClass($entity);
        if (!$reflection->hasProperty('usergroups')) {
            return;
        }

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
        $entity  = $this->mapper->findOneBySysName($name);
        if ($entity) {
            $this->logger->debug(sprintf(
                'Found existing user group `%d` with title `%s` by sys name `%s`',
                $entity->getId(), $entity->getTitle(), $sysName
            ));

            return $entity;
        }

        // try to fetch usergroup by title
        $entity = $this->mapper->findOneByTitle($name);
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
        $entity->setIsAgentGroup($isAgent);

        $this->persister->persistAndFlush($entity);

        return $entity;
    }
}

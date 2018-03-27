<?php

namespace DpBehat;

use Application\DeskPRO\Entity\Permission;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Usergroup;
use DpBehat\Data\DataContext;

/**
 * Class PermissionContext.
 */
class PermissionContext extends BaseContext
{
    /**
     * @Given I remove :person usergroup relation :sysName
     *
     * @param string $who
     * @param string $sysName
     */
    public function iRemoveUserGroup($who, $sysName)
    {
        DataContext::scheduleCleanup();

        $person = DataContext::hasReference($who) ? DataContext::getReference($who) : $this->getPerson($who);

        foreach ($person->getUsergroups() as $usergroup) {
            if ($usergroup->getSysName() === $sysName) {
                $person->getUsergroups()->removeElement($usergroup);
            }
        }

        $this->em()->persist($person);
        $this->em()->flush();
    }

    /**
     * @Given I add :person usergroup relation :sysName
     *
     * @param string $who
     * @param string $sysName
     */
    public function iAddUserGroup($who, $sysName)
    {
        DataContext::scheduleCleanup();

        $person = DataContext::hasReference($who) ? DataContext::getReference($who) : $this->getPerson($who);

        $usergroup = $this->repository(Usergroup::class)->findOneBy(['sys_name' => $sysName]);
        if (!$person) {
            throw new \RuntimeException('Unable to get usergroup '.$sysName);
        }

        if (!$person->getUsergroups()->contains($usergroup)) {
            $person->getUsergroups()->add($usergroup);
            $this->em()->persist($person);
            $this->em()->flush();
        }
    }

    /**
     * @Given I set permission :permissionName = :value for :sysName usergroup
     *
     * @param string $permissionName
     * @param string $value
     * @param string $sysName
     */
    public function iSetUserGroupPermission($permissionName, $value, $sysName)
    {
        DataContext::scheduleCleanup();

        $connection = $this->em()->getConnection();
        $group_ids  = $connection->fetchAllCol('SELECT id FROM usergroups WHERE sys_name = ?', [$sysName]);

        foreach ($group_ids as $gid) {
            $connection->executeUpdate(
                'DELETE FROM permissions WHERE usergroup_id = ? AND name = ?',
                [$gid, $permissionName]
            );
            if ($value) {
                $connection->executeUpdate(
                    'INSERT INTO permissions SET usergroup_id = ?, name = ?, value = ?',
                    [$gid, $permissionName, $value]
                );
            }
        }

        $connection->executeUpdate('DELETE FROM permissions_cache');
    }

    /**
     * @Given I set only :permissionName = :value for :sysName usergroup
     *
     * @param string $permissionName
     * @param string $value
     * @param string $sysName
     */
    public function iSetOnlyUserGroupPermission($permissionName, $value, $sysName)
    {
        $this->iClearUserGroupPermissions($sysName);
        $this->iSetUserGroupPermission($permissionName, $value, $sysName);
    }

    /**
     * @Given I clear usergroup :sysName permissions
     *
     * @param string $sysName
     */
    public function iClearUserGroupPermissions($sysName)
    {
        DataContext::scheduleCleanup();
        $connection = $this->em()->getConnection();
        $group_ids  = $connection->fetchAllCol('SELECT id FROM usergroups WHERE sys_name = ?', [$sysName]);
        foreach ($group_ids as $gid) {
            $connection->executeUpdate('DELETE FROM permissions WHERE usergroup_id = ?', [$gid]);
        }
        $connection->executeUpdate('DELETE FROM permissions_cache');
    }

    /**
     * @Given I grant the :departmentId department permission of :app app for :who
     *
     * @param string $who
     * @param string $departmentId
     * @param string $app
     */
    public function iGrantDepartmentPermissionForUser($departmentId, $who, $app)
    {
        DataContext::scheduleCleanup();

        $departmentId = DataContext::replace($departmentId);
        $person       = $this->getPerson($who);

        $connection = $this->em()->getConnection();
        $connection->executeUpdate(
            'INSERT IGNORE INTO department_permissions SET department_id = ?, person_id = ?, app = ?, name="full", value=1, is_active=1',
            [$departmentId, $person->getId(), $app]
        );
    }

    /**
     * @Given I grant the :departmentId department permission of :app app for usergroup :usergroup
     *
     * @param string $usergroup
     * @param string $departmentId
     * @param string $app
     */
    public function iGrantDepartmentPermissionForUsergroup($departmentId, $usergroup, $app)
    {
        DataContext::scheduleCleanup();

        $departmentId = DataContext::replace($departmentId);
        $usergroup    = DataContext::getReference($usergroup.'_group');

        $connection = $this->em()->getConnection();
        $connection->executeUpdate(
            'INSERT IGNORE INTO department_permissions SET department_id = ?, usergroup_id = ?, app = ?, name="full", value=1, is_active=1',
            [$departmentId, $usergroup->getId(), $app]
        );
    }

    /**
     * @Given I clear department permissions for :who
     *
     * @param $who
     */
    public function iClearDepartmentPermissionsForWho($who)
    {
        DataContext::scheduleCleanup();
        $person = $this->getPerson($who);

        $connection = $this->em()->getConnection();
        $connection->executeUpdate(
            'DELETE FROM department_permissions WHERE person_id = ?',
            [$person->getId()]
        );
    }

    /**
     * @Given I grant the :feedbackCategoryId feedback category permission for usergroup :usergroup
     *
     * @param string $usergroup
     * @param string $departmentId
     * @param string $app
     */
    public function iGrantFeedbackCategoryPermissionForUsergroup($feedbackCategoryId, $usergroup)
    {
        DataContext::scheduleCleanup();

        $feedbackCategoryId = DataContext::replace($feedbackCategoryId);
        $usergroup          = DataContext::getReference($usergroup.'_group');

        $connection = $this->em()->getConnection();
        $connection->executeUpdate(
            'INSERT IGNORE INTO feedback_category2usergroup SET category_id = ?, usergroup_id = ?',
            [$feedbackCategoryId, $usergroup->getId()]
        );
    }

    /**
     * @Given I clear permissions for :who
     *
     * @param $who
     */
    public function iClearPermissionsForWho($who)
    {
        DataContext::scheduleCleanup();
        $person = $this->getPerson($who);

        $connection = $this->em()->getConnection();
        $connection->executeUpdate(
            'DELETE FROM permissions WHERE person_id = ?',
            [$person->getId()]
        );
    }

    /**
     * @Given I have permissions to use :entity
     *
     * @param string $entity
     */
    public function iHavePermissionsToUse($entity)
    {
        /** @var Person $person */
        $person     = DataContext::getReference('me');
        $permission = new Permission();
        $permission
            ->setValue(true)
            ->setName(strtolower($entity).'.use')
            ->setPerson($person);

        $this->em()->persist($permission);
        $this->em()->flush();
    }

    /**
     * @param string $who
     *
     * @throws \Exception
     *
     * @return Person
     */
    private function getPerson($who)
    {
        $person = $this->get('user_details')->getWho($who);
        if (!$person) {
            $person = DataContext::getReference($who);
        }
        $this->em()->refresh($person);

        return $person;
    }
}

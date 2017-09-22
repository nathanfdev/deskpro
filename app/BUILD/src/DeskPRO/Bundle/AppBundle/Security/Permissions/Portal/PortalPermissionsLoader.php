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

namespace DeskPRO\Bundle\AppBundle\Security\Permissions\Portal;

use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\DepartmentPermission;
use Application\DeskPRO\Entity\DownloadCategory;
use Application\DeskPRO\Entity\FeedbackCategory;
use Application\DeskPRO\Entity\Guide;
use Application\DeskPRO\Entity\NewsCategory;
use Application\DeskPRO\Entity\Permission;
use Application\DeskPRO\Entity\Usergroup;
use Application\DeskPRO\EntityRepository\Guide as GuideRepository;
use Application\DeskPRO\EntityRepository\Helper\CategoryHierarchy;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;

/**
 * This is an adapter into the "old" permissions storage system.
 */
class PortalPermissionsLoader
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var array
     */
    private $usergroupsCache = [];

    /**
     * @var array
     */
    private $departmentsCache = [];

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Returns a list of usergroups permissions.
     *
     * @param array $userGroups
     *
     * @return Permission[]
     */
    public function getUsergroupPermissions(array $userGroups)
    {
        $userGroups = new ArrayCollection($this->em->getRepository(Usergroup::class)->findBy([
            'id' => $userGroups,
        ]));

        // add 'everyone' and 'registered' group inherit permissions
        $everyoneGroup = $this->em->getRepository(Usergroup::class)->findOneBy([
            'sys_name' => Usergroup::EVERYONE,
        ]);
        $registeredGroup = $this->em->getRepository(Usergroup::class)->findOneBy([
            'sys_name' => Usergroup::REGISTERED,
        ]);

        if ($everyoneGroup && $everyoneGroup->isEnabled() && !$userGroups->contains($everyoneGroup)) {
            $userGroups->add($everyoneGroup);
        }
        if ($registeredGroup && $registeredGroup->isEnabled() && !$userGroups->contains($registeredGroup)) {
            $customGroups = $userGroups->filter(function (Usergroup $userGroup) {
                return !in_array($userGroup->getSysName(), [Usergroup::EVERYONE, Usergroup::REGISTERED]);
            });

            if ($customGroups->count() > 0) {
                $userGroups->add($registeredGroup);
            }
        }

        return $this->em->getRepository(Permission::class)->findBy([
            'usergroup' => $userGroups->toArray(),
            'person'    => null,
        ]);
    }

    /**
     * @param array $userGroups
     *
     * @return mixed
     */
    public function getAllowedFeedbackCategories(array $userGroups)
    {
        return $this->getAllowedCategories(FeedbackCategory::class, $userGroups);
    }

    /**
     * @param array $userGroups
     *
     * @return mixed
     */
    public function getAllowedNewsCategories(array $userGroups)
    {
        return $this->getAllowedCategories(NewsCategory::class, $userGroups);
    }

    /**
     * @param array $userGroups
     *
     * @return mixed
     */
    public function getAllowedArticleCategories(array $userGroups)
    {
        return $this->getAllowedCategories(ArticleCategory::class, $userGroups);
    }

    /**
     * @param array $userGroups
     *
     * @return mixed
     */
    public function getAllowedDownloadCategories(array $userGroups)
    {
        return $this->getAllowedCategories(DownloadCategory::class, $userGroups);
    }

    /**
     * @param array $userGroups
     *
     * @return array
     */
    public function getAllowedTicketDepartments(array $userGroups)
    {
        return $this->getAllowedUsergroupDepartments($userGroups, DepartmentPermission::APP_TICKETS);
    }

    /**
     * @param array $userGroups
     *
     * @return array
     */
    public function getAllowedChatDepartments(array $userGroups)
    {
        return $this->getAllowedUsergroupDepartments($userGroups, DepartmentPermission::APP_CHAT);
    }

    /**
     * @param array $userGroups
     *
     * @return array
     */
    public function getAllowedGuides(array $userGroups)
    {
        /** @var GuideRepository $repository */
        $repository = $this->em->getRepository(Guide::class);

        return $repository->getGuidesForUsergroups($userGroups);
    }

    /**
     * @param string $entityClass
     * @param array  $userGroups
     *
     * @return array
     */
    public function getAllowedCategories($entityClass, array $userGroups)
    {
        /** @var CategoryHierarchy $repository */
        $repository = $this->em->getRepository($entityClass);

        return $repository->getCategoriesForUsergroups($userGroups);
    }

    /**
     * @param array  $userGroups
     * @param string $app
     *
     * @return mixed
     */
    public function getAllowedUsergroupDepartments(array $userGroups, $app)
    {
        $userGroupIds = array_map(function ($userGroup) {
            return $userGroup instanceof Usergroup ? $userGroup->getId() : (int) $userGroup;
        }, $userGroups);

        $cacheKey = md5(serialize([$userGroupIds, $app]));
        if (!isset($this->usergroupsCache[$cacheKey])) {
            // load department permissions
            $permissions = $this->em->getConnection()->fetchAll(
                "SELECT dp.name, dp.value, dp.department_id
            FROM department_permissions dp
            JOIN departments d ON dp.department_id = d.id
            WHERE dp.usergroup_id IN (:usergroup_ids) AND dp.is_active = 1 AND dp.value = 1 AND d.is_{$app}_enabled = 1",

                ['usergroup_ids' => $userGroupIds],
                ['usergroup_ids' => Connection::PARAM_INT_ARRAY]
            );

            $this->usergroupsCache[$cacheKey] = $this->getAllowedPermissionDepartments($permissions, $app);
        }

        return $this->usergroupsCache[$cacheKey];
    }

    /**
     * @param array  $permissions
     * @param string $app
     *
     * @return array
     */
    public function getAllowedPermissionDepartments(array $permissions, $app)
    {
        $permissionMap = [];
        foreach ($permissions as $permission) {
            $permissionMap[$permission['department_id']][] = [
                'name'  => $permission['name'],
                'value' => $permission['value'],
            ];
        }

        // load departments
        if (!isset($this->departmentsCache[$app])) {
            $departments = $this->em->getConnection()->fetchAll(
                "SELECT id, parent_id FROM departments WHERE is_{$app}_enabled = 1"
            );

            $this->departmentsCache[$app] = [];
            foreach ($departments as $department) {
                $this->departmentsCache[$app][$department['id']] = $department;
            }
        }

        $departmentParents  = [];
        $departmentChildren = [];
        foreach ($this->departmentsCache[$app] as $department) {
            if ($department['parent_id'] && isset($this->departmentsCache[$app][$department['parent_id']])) {
                $departmentParents[$department['id']][$department['parent_id']]  = true;
                $departmentChildren[$department['parent_id']][$department['id']] = true;
            }
        }

        // prepare departments with permission list
        $departmentsWithPermissions = array_fill_keys(array_keys($permissionMap), true);

        // add also parent nodes
        $addParentIterator = function (array $departmentIds) use (&$departmentsWithPermissions, $departmentParents, &$addParentIterator) {
            foreach ($departmentIds as $departmentId => $val) {
                if (!isset($departmentParents[$departmentId])) {
                    continue;
                }

                foreach ($departmentParents[$departmentId] as $parentId => $val2) {
                    $departmentsWithPermissions[$parentId] = true;
                }

                $addParentIterator($departmentParents[$departmentId]);
            }
        };

        $addParentIterator($departmentsWithPermissions);

        // remove empty parent nodes
        $hasChildIterator = function ($departmentId) use ($departmentsWithPermissions, $departmentChildren, &$hasChildIterator) {
            if (!isset($departmentChildren[$departmentId])) {
                return false;
            }

            foreach ($departmentChildren[$departmentId] as $childId => $val) {
                // check current leaf children level
                if (!isset($departmentChildren[$childId])) {
                    if (isset($departmentsWithPermissions[$childId])) {
                        return true;
                    }
                } else {
                    // check deeper
                    if ($hasChildIterator($childId)) {
                        return true;
                    }
                }
            }

            return false;
        };

        foreach ($departmentsWithPermissions as $departmentId => $val) {
            // leaf department, no need to check
            if (!isset($departmentChildren[$departmentId])) {
                continue;
            }

            // check that leaf departments of this parent department has permissions
            if (!$hasChildIterator($departmentId)) {
                unset($departmentsWithPermissions[$departmentId]);
            }
        }

        // prepare result permissions
        $result = [];

        foreach ($departmentsWithPermissions as $departmentId => $val) {
            // get own permissions
            $departmentPermissions = isset($permissionMap[$departmentId]) ? $permissionMap[$departmentId] : [];

            // if it's a parent department then get permissions from its children
            $childPermissionIterator = function ($departmentId) use (&$departmentPermissions, $departmentChildren, $permissionMap, &$childPermissionIterator) {
                if (!isset($departmentChildren[$departmentId])) {
                    return;
                }

                foreach ($departmentChildren[$departmentId] as $childId => $val) {
                    $childPermissions = isset($permissionMap[$childId]) ? $permissionMap[$childId] : [];
                    foreach ($childPermissions as $childPermission) {
                        $departmentPermissions[] = $childPermission;
                    }

                    $childPermissionIterator($childId);
                }
            };

            $childPermissionIterator($departmentId);

            // calculate permissions
            $result[$departmentId] = Permission::getEffectivePermissions($departmentPermissions);
        }

        return $result;
    }
}

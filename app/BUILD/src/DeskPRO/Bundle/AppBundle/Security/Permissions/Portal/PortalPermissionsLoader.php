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

use Application\DeskPRO\Cache\ConvenientCache;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\DepartmentPermission;
use Application\DeskPRO\Entity\DownloadCategory;
use Application\DeskPRO\Entity\FeedbackCategory;
use Application\DeskPRO\Entity\NewsCategory;
use Application\DeskPRO\Entity\Permission;
use Application\DeskPRO\EntityRepository\Helper\CategoryHierarchy;
use DeskPRO\Bundle\AppBundle\Helper\ArbitraryHasher;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;

/**
 * This is an adapter into the "old" permissions storage system.
 */
class PortalPermissionsLoader
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
     * @var EntityManager
     */
    private $em;

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
        return $this->em->getRepository(Permission::class)->findBy([
            'usergroup' => $userGroups,
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
        return $this->getAllowedDepartments($userGroups, DepartmentPermission::APP_TICKETS);
    }

    /**
     * @param array $userGroups
     *
     * @return array
     */
    public function getAllowedChatDepartments(array $userGroups)
    {
        return $this->getAllowedDepartments($userGroups, DepartmentPermission::APP_CHAT);
    }

    /**
     * @param string $entityClass
     * @param array  $userGroups
     *
     * @return array
     */
    private function getAllowedCategories($entityClass, array $userGroups)
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
    private function getAllowedDepartments(array $userGroups, $app)
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('dp')
            ->from(DepartmentPermission::class, 'dp')
            ->join('dp.department', 'd')
            ->where(
                'dp.usergroup IN (:usergroup_ids)',
                'dp.is_active = 1',
                'dp.value = 1',
                "d.is_{$app}_enabled = 1"
            )
            ->setParameter('usergroup_ids', $userGroups)
        ;

        /** @var DepartmentPermission[] $permissions */
        $permissions   = $qb->getQuery()->getResult();
        $permissionMap = [];

        /** @var Department[]|ArrayCollection $departments */
        $departments = new ArrayCollection();
        foreach ($permissions as $permission) {
            $department   = $permission->getDepartment();
            $departmentId = $department->getId();

            $departments->offsetSet($departmentId, $department);
            $permissionMap[$departmentId][] = $permission;
        }

        // add also parent nodes
        foreach ($departments as $department) {
            foreach ($department->getAllParents() as $parent) {
                $departments->add($parent);
            }
        }

        // remove empty parent nodes
        foreach ($departments as $department) {
            if ($department->isLeaf()) {
                continue;
            }

            $foundAllowedChild = false;
            foreach ($department->getAllChildren() as $child) {
                if ($child->isLeaf() && $departments->contains($child)) {
                    $foundAllowedChild = true;
                }
            }

            if (!$foundAllowedChild) {
                $departments->removeElement($department);
            }
        }

        $result = [];
        foreach ($departments as $department) {
            $departmentId = $department->getId();

            /** @var DepartmentPermission[]|ArrayCollection $departmentPermissions */
            $departmentPermissions = isset($permissionMap[$departmentId]) ? $permissionMap[$departmentId] : [];

            // get own permissions
            foreach ($departmentPermissions as $permission) {
                $result[$departmentId][$permission->getName()] = 1;
            }

            // if it's a parent department then get permissions from its children
            if (!$department->isLeaf()) {
                foreach ($department->getAllChildren() as $child) {
                    if ($departments->contains($child)) {
                        /** @var DepartmentPermission[] $childPermissions */
                        $childPermissions = isset($permissionMap[$child->getId()]) ? $permissionMap[$child->getId()] : [];

                        foreach ($childPermissions as $permission) {
                            $result[$departmentId][$permission->getName()] = 1;
                        }
                    }
                }
            }
        }

        return $result;
    }
}

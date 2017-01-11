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

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Service;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\ReportDashboard as DashboardEntity;
use Application\DeskPRO\Entity\ReportDashboardPermission as Permission;
use Application\DeskPRO\EntityRepository\Person as PersonRepository;
use Doctrine\ORM\EntityManager;

class DashboardPermissions
{
    const PERMISSION_FULL = 2;

    const PERMISSION_VIEW = 1;

    const PERMISSION_NONE = 0;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param Person          $person
     * @param DashboardEntity $dashboard
     *
     * @return mixed
     */
    public function isAllowedToEdit(Person $person, DashboardEntity $dashboard)
    {
        $person->loadHelper('DashboardPermissions');

        return $person->getHelper('DashboardPermissions')->isAllowedToEdit($dashboard);
    }

    /**
     * @param Person          $person
     * @param DashboardEntity $dashboard
     *
     * @return mixed
     */
    public function isAllowedToView(Person $person, DashboardEntity $dashboard)
    {
        $person->loadHelper('DashboardPermissions');

        return $person->getHelper('DashboardPermissions')->isAllowedToView($dashboard);
    }

    /**
     * @param DashboardEntity $dashboard
     */
    public function getPermissions(DashboardEntity $dashboard)
    {
        $this->em
            ->getRepository(Permission::class)
            ->findBy(
                [
                    'dashboard' => $dashboard->getId(),
                ]
            );
    }

    /**
     * @param Person          $person
     * @param DashboardEntity $dashboard
     *
     * @return Permission[]|Permission
     */
    public function getPersonPermissions(Person $person, DashboardEntity $dashboard = null)
    {
        $criteria = [
            'person' => $person->getId(),
        ];
        if ($dashboard) {
            $criteria['dashboard'] = $dashboard->getId();
            $permissions           = $this->em->getRepository(Permission::class)->findOneBy(
                $criteria
            );
        } else {
            $permissions = $this->em->getRepository(Permission::class)->findBy(
                $criteria
            );
        }

        if ($permissions) {
            return $permissions;
        }

        return false;
    }

    /**
     * @param Person          $person
     * @param DashboardEntity $dashboard
     * @param string          $permission
     */
    public function setPermissions(Person $person, DashboardEntity $dashboard, $permission)
    {
        $personPermissions = $this->getPersonPermissions($person, $dashboard);
        if (!$personPermissions && $permission !== self::PERMISSION_NONE) {
            $personPermissions = new Permission();
            $personPermissions->setDashboard($dashboard)->setPerson($person);
        }
        if ($personPermissions) {
            switch ($permission) {
                case self::PERMISSION_FULL:
                    $personPermissions->setName(Permission::FULL);
                    $this->save($personPermissions);
                    break;
                case self::PERMISSION_VIEW:
                    $personPermissions->setName(Permission::VIEW);
                    $this->save($personPermissions);
                    break;
                case self::PERMISSION_NONE:
                    $this->em->remove($personPermissions);
                    $this->em->flush($personPermissions);
                    break;
            }
        }
    }

    /**
     * @param $permissions
     *
     * @return int
     */
    protected function mapPermissions($permissions)
    {
        switch ($permissions) {
            case Permission::FULL:
                return self::PERMISSION_FULL;
            case Permission::VIEW:
                return self::PERMISSION_VIEW;
            default:
                return self::PERMISSION_NONE;
        }
    }

    /**
     * @param DashboardEntity $dashboard
     *
     * @return Permission[]
     */
    protected function getDashboardPermissions(DashboardEntity $dashboard)
    {
        /** @var Permission[] $permissions */
        $permissions = $this->em->getRepository(Permission::class)->findBy(
            ['dashboard' => $dashboard->getId()]
        );

        return $permissions;
    }

    /**
     * @return \Application\DeskPRO\Entity\Person[]
     */
    protected function getAllAgents()
    {
        /** @var PersonRepository $personRepository */
        $personRepository = $this->em->getRepository(Person::class);
        $agents           = $personRepository->getAgents();

        return $agents;
    }

    /**
     * @param $agent_id
     *
     * @return Person
     */
    public function getAgent($agent_id)
    {
        /** @var PersonRepository $personRepository */
        $personRepository = $this->em->getRepository(Person::class);

        return $personRepository->getAgent($agent_id);
    }

    /**
     * @return array
     */
    public function getNewDashboardPermissions()
    {
        $agents = $this->getAllAgents();
        $data   = [];
        foreach ($agents as $agent) {
            /* @var Person $agent */
            $data[$agent->getId()] = [
                'permissions' => self::PERMISSION_NONE,
                'id'          => $agent->getId(),
            ];
            $data[$agent->getId()]['name']   = $agent->getDisplayName();
            $data[$agent->getId()]['avatar'] = $agent->getPictureUrl();
        }
        $data = array_values($data);

        return $data;
    }

    /**
     * @param DashboardEntity $dashboard
     *
     * @return array
     */
    public function getApiDashboardPermissions(DashboardEntity $dashboard)
    {
        $permissions = $this->getDashboardPermissions($dashboard);
        $agents      = $this->getAllAgents();
        $data        = [];

        foreach ($permissions as $permission) {
            if (isset($agents[$permission->getAgent()->getId()])) {
                $data[$permission->getAgent()->getId()] = [
                    'permissions' => $this->mapPermissions($permission->getName()),
                    'id'          => $permission->getAgent()->getId(),
                ];
            }
        }
        foreach ($agents as $agent) {
            /** @var Person $agent */
            if (!isset($data[$agent->getId()])) {
                $data[$agent->getId()] = [
                    'permissions' => self::PERMISSION_NONE,
                    'id'          => $agent->getId(),
                ];
            }
            $data[$agent->getId()]['name']   = $agent->getDisplayName();
            $data[$agent->getId()]['avatar'] = $agent->getPictureUrl();
        }
        $data = array_values($data);

        return $data;
    }

    /**
     * @param DashboardEntity $dashboard
     * @param DashboardEntity $prototype
     */
    public function clonePermissions(DashboardEntity $dashboard, DashboardEntity $prototype)
    {
        $reportDashboardPermissions = $this->getDashboardPermissions($prototype);
        foreach ($reportDashboardPermissions as $permission_prototype) {
            $permission = new Permission();
            $permission
                ->setDashboard($dashboard)
                ->setPerson($permission_prototype->getPerson())
                ->setName($permission_prototype->getName());
            $this->save($permission);
        }
    }

    /**
     * @param DashboardEntity $dashboard
     *
     * @return bool
     */
    public function isEditableDashboard(DashboardEntity $dashboard)
    {
        return !$dashboard->isDefault();
    }

    /**
     * @param $permission
     */
    public function save($permission)
    {
        $this->em->persist($permission);
        $this->em->flush();
    }

    /**
     * @param Permission $permission
     */
    public function delete(Permission $permission)
    {
        $this->em->remove($permission);
        $this->em->flush();
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: Den
 * Date: 16.12.2014
 * Time: 2:30
 */

namespace Application\ApiBundle\Service;

use \Doctrine\ORM\EntityManager;

use Application\DeskPRO\Entity\ReportDashboard as DashboardEntity;
use Application\DeskPRO\Entity\Person as PersonEntity;
use Application\DeskPRO\Entity\ReportDashboardPermission as Permission;
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
     * @param PersonEntity    $person
     * @param DashboardEntity $dashboard
     *
     * @return mixed
     */
    public function isAllowedToEdit(PersonEntity $person, DashboardEntity $dashboard)
    {
        $person->loadHelper('DashboardPermissions');
        return $person->getHelper('DashboardPermissions')->isAllowedToEdit($dashboard);
    }

    /**
     * @param PersonEntity    $person
     * @param DashboardEntity $dashboard
     *
     * @return mixed
     */
    public function isAllowedToView(PersonEntity $person, DashboardEntity $dashboard)
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
            ->getRepository('DeskPRO:ReportDashboardPermission')
            ->findBy(
                array(
                    'dashboard'=>$dashboard->getId(),
                )
            );
    }

    /**
     * @param PersonEntity    $person
     * @param DashboardEntity $dashboard
     *
     * @return Permission[]|Permission
     */
    public function getPersonPermissions(PersonEntity $person, DashboardEntity $dashboard = null)
    {
        $criteria = array(
            'person' => $person->getId(),
        );
        if($dashboard) {
            $criteria['dashboard'] = $dashboard->getId();
            $permissions = $this->em->getRepository('DeskPRO:ReportDashboardPermission')->findOneBy(
                $criteria
            );
        } else {
            $permissions = $this->em->getRepository('DeskPRO:ReportDashboardPermission')->findBy(
                $criteria
            );
        }

        if($permissions){
            return $permissions;
        }
        return false;
    }

    /**
     * @param PersonEntity    $person
     * @param DashboardEntity $dashboard
     * @param string          $permission
     */
    public function setPermissions(PersonEntity $person, DashboardEntity $dashboard, $permission)
    {
        $personPermissions = $this->getPersonPermissions($person, $dashboard);
        switch($permission)
        {
            case self::PERMISSION_FULL:
                $personPermissions->setName(Permission::FULL);
                $this->save($personPermissions);
                break;
            case self::PERMISSION_VIEW:
                $personPermissions->setName(Permission::VIEW);
                $this->save($personPermissions);
                break;
            case self::PERMISSION_NONE:
                break;
        }
    }

    protected function mapPermissions($permissions)
    {
        switch($permissions)
        {
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
        $permissions = $this->em->getRepository('DeskPRO:ReportDashboardPermission')->findBy(
            array('dashboard' => $dashboard->getId())
        );
        return $permissions;
    }


    public function getApiDashboardPermissions(DashboardEntity $dashboard)
    {

        $permissions = $this->getDashboardPermissions($dashboard);
        $data = array();

        foreach($permissions as $permission)
        {
            $data[$permission->getAgent()->getId()] = array(
                'permissions' => $this->mapPermissions($permission->getName()),
                'id' => $permission->getAgent()->getId(),
            );
        }
        $persons = $this->em->getRepository('DeskPRO:Person')->findBy(
            array('id' => array_keys($data))
        );
        foreach($persons as $person)
        {
            /** @var PersonEntity $person */
            $data[$person->getId()]['name'] = $person->getDisplayName();
            $data[$person->getId()]['avatar'] = $person->getPictureUrl();
        }
        $data = array_values($data);
        return $data;

    }

    public function clonePermissions(DashboardEntity $dashboard)
    {
        $reportDashboardPermissions = $this->getDashboardPermissions($dashboard);
        foreach($reportDashboardPermissions as $permission_prototype)
        {
            $permission = new Permission();
            $permission
                ->setDashboard($dashboard)
                ->setPerson($permission_prototype->getPerson())
                ->setName($permission_prototype->getName());
            $this->save($permission);
        }
    }

    public function save($permission)
    {
        if(is_array($permission))
        {

        }
        $this->em->persist($permission);
        $this->em->flush();
    }

    public function delete(Permission $permission)
    {
        $this->em->remove($permission);
        $this->em->flush();
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: Den
 * Date: 16.12.2014
 * Time: 2:30
 */

namespace Application\ApiBundle\Service;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use \Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;

use Application\DeskPRO\Dpql\Statement\Display;

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
                    'dashboard_id'=>$dashboard->getId(),
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
            'person_id' => $person->getId(),
        );
        if($dashboard) {
            $criteria['dashboard_id'] = $dashboard->getId();
            $resolve = function($permissions) {
                return array_shift($permissions);
            };
        } else {
            $resolve = function($permissions) {
                return $permissions;
            };
        }
        $permissions = $this->em->getRepository('DeskPRO:ReportDashboardPermission')->findBy(
            $criteria
        );
        if($permissions){
            return $resolve($permissions);
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

        $criteria = array(
            'dashboard_id' => $dashboard->getId(),
            'person_id'    => $person->getId()
        );
        $permissions = $this->em->getRepository('DeskPRO:ReportDashboardPermission')->findBy($criteria);
        if(!$personPermissions) {
            $permissions = new Permission();
            $permissions->setPerson($person)
                ->setDashboard($dashboard);
        } else {
            $permissions = array_shift($permissions);
        }
        $permissions->setName($this->mapPermissions($permission));

        switch($permissions)
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
        $this->em->getRepository('DeskPRO:ReportDashboardPermission');
    }

    protected function mapPermissions($permissions)
    {
        switch($permissions)
        {
            case Permission::FULL:
                return self::PERMISSION_FULL;
            case Permission::VIEW:
                return self::PERMISSION_VIEW;
            case self::PERMISSION_FULL:
                return Permission::FULL;
            case self::PERMISSION_VIEW:
                return Permission::VIEW;
            default:
                return self::PERMISSION_NONE;
        }
    }

    public function getDashboardPermissions(DashboardEntity $dashboard)
    {
        /** @var Permission[] $permissions */
        $permissions = $this->em->getRepository('DeskPRO:ReportDashboardPermission')->findBy(
            array('dashboard_id' => $dashboard->getId())
        );

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
        return array_values($data);

    }

    public function save(Permission $permission)
    {

        $this->em->persist($permission);
        $this->em->flush();
    }

    public function delete(Permission $permission)
    {
        $this->em->remove($permission);
        $this->em->flush();
    }
}
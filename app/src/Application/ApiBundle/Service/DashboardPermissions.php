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
        return $resolve($permissions);
    }

    /**
     * @param PersonEntity    $person
     * @param DashboardEntity $dashboard
     * @param string          $permissions
     */
    public function setPermissions(PersonEntity $person, DashboardEntity $dashboard, $permissions)
    {
        $permissions = $this->getPersonPermissions($person, $dashboard);
        switch($permissions)
        {
            case self::PERMISSION_FULL:
                $permissions->setName(Permission::FULL);
                $this->save($permissions);
                break;
            case self::PERMISSION_VIEW:
                $permissions->setName(Permission::FULL);
                $this->save($permissions);
                break;
            case self::PERMISSION_NONE:
                break;
        }
        $this->em->getRepository('DeskPRO:ReportDashboardPermission');
    }

    public function save(Permission $permissions)
    {
        $this->em->persist($permissions);
        $this->em->flush();
    }

    public function delete(Permission $permission)
    {
        $this->em->remove($permission);
        $this->em->flush();
    }
}
<?php

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\People\Helpers;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

class DashboardPermissions implements \Orb\Helper\ShortCallableInterface
{
    /** @var \Application\DeskPRO\Entity\Person */
    protected $person;

    /** @var array|null */
    protected $_allowed_ids = null;
    /** @var array */
    protected $_disallowed_ids = array();

    public function __construct(Entity\Person $person)
    {
        $this->person = $person;
    }

    public function getShortCallableNames()
    {
        return array(
            'isAllowedToEdit' => 'isAllowedToEdit',
            'isAllowedToView' => 'isAllowedToView',
        );
    }

    public function isAllowedToEdit(Entity\ReportDashboard $dashboard)
    {
        $permissions = App::$container
            ->getEm()
            ->getRepository('DeskPRO:ReportDashboardPermission')
            ->findBy(
                array(
                    'dashboard'=>$dashboard->getId(),
                    'person'=>$this->person->getId(),
                    'name' => Entity\ReportDashboardPermission::FULL,
                )
            );
        if($permissions) {
            return true;
        } else {
            return false;
        }
    }

    public function isAllowedToView(Entity\ReportDashboard $dashboard)
    {
        $permissions = App::$container
            ->getEm()
            ->getRepository('DeskPRO:ReportDashboardPermission')
            ->findBy(
                array(
                    'dashboard'=>$dashboard->getId(),
                    'person'=>$this->person->getId(),
                )
            );
        if($permissions) {
            return true;
        } else {
            return false;
        }
    }
}
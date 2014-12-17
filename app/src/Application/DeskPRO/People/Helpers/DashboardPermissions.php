<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

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
                    'dashboard_id'=>$dashboard->getId(),
                    'person_id'=>$this->person->getId(),
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
                    'dashboard_id'=>$dashboard->getId(),
                    'person_id'=>$this->person->getId(),
                    'name' => array(Entity\ReportDashboardPermission::FULL, Entity\ReportDashboardPermission::VIEW)
                )
            );
        if($permissions) {
            return true;
        } else {
            return false;
        }
    }
}
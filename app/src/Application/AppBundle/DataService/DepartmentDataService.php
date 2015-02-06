<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
 * @subpackage
 */

namespace Application\AppBundle\DataService;


use Application\AuthBundle\Permissions\Portal\PortalPermissionsManager;
use Application\DeskPRO\Entity\Person;
use Doctrine\ORM\EntityManager;

class DepartmentDataService extends AbstractDataService
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Application\AuthBundle\Permissions\Portal\PortalPermissionsManager
     */
    private $portal_permissions_manager;

    public function __construct(EntityManager $em, PortalPermissionsManager $portal_permissions_manager)
    {
        $this->em = $em;
        $this->portal_permissions_manager = $portal_permissions_manager;
    }

    public function getAuthorizedDepartmentsForPersonInPortal(Person $person)
    {
        $portal_permissions_manager = $this->portal_permissions_manager;
        $em = $this->em;

        return $this->generateAndCache(
            $person,
            function() use ($person, $portal_permissions_manager, $em) {
                $allowed_department_ids = $portal_permissions_manager->getAllowedDepartmentIds($person);

                // TODO: make sure allowed_department_ids is correct
                $departments = $em
                    ->getRepository('DeskPRO:Department')
                    ->createQueryBuilder('d')
                    ->select('d')
                    ->where('d.id IN (:allowed_department_ids) AND d.parent IS NULL AND d.is_tickets_enabled = true')
                    ->orderBy('d.display_order', 'ASC')
                    ->setParameter('allowed_department_ids', $allowed_department_ids)
                    ->getQuery()
                    ->getResult();

                return $departments;
            }
        );
    }
}
 
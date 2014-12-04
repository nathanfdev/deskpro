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

namespace Application\FormBundle\TicketLayout;


use Application\DeskPRO\Entity\Department;
use Doctrine\ORM\EntityManager;
use Application\DeskPRO\Entity\TicketLayout;

class TicketLayoutFactory
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entity_manager;

    public function __construct(EntityManager $entity_manager)
    {
        $this->entity_manager = $entity_manager;
    }

    /**
     * Used in the TicketType form type to detect the layout it should use, given the selected dept (or null for initial layout)
     *
     * @param Department|int $department the department entity or its ID
     * @return TicketLayout
     */
    public function getLayoutForTicketForm($department = null)
    {
        if ($department) {
            if ($department_entity = $this->entity_manager->createQuery('SELECT l FROM DeskPRO:TicketLayout l WHERE l.department = :department')
                ->setParameter('department', $department)
                ->getOneOrNullResult()) {
                return $department_entity;
            }
        }


        return $this->getInitialLayout();
    }

    /**
     * @return TicketLayout
     */
    public function getInitialLayout()
    {
        // TODO: what if all departments are custom layouts, ie there is no default layout? something different should happen.
        return $this->entity_manager->createQuery('SELECT l FROM DeskPRO:TicketLayout l WHERE l.department IS NULL')
            ->getOneOrNullResult();
    }
}
 
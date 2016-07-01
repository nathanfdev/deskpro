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

namespace DeskPRO\Bundle\AppBundle\DataService;

use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Security\Permissions\PermissionsManager;
use Doctrine\ORM\EntityManager;

/**
 * Class DepartmentDataService.
 */
class DepartmentDataService extends AbstractDataService
{
    /**
     * @var \DeskPRO\Bundle\AppBundle\Security\Permissions\PermissionsManager
     */
    private $permissions_manager;

    /**
     * Constructor.
     *
     * @param EntityManager      $em
     * @param PermissionsManager $permissionsManager
     */
    public function __construct(EntityManager $em, PermissionsManager $permissionsManager)
    {
        parent::__construct($em);

        $this->permissions_manager = $permissionsManager;
    }

    /**
     * An array of departments
     * that are allowed for this person in tickets.
     *
     * If a department that is returned has a parent, the calling code is expected to
     * deal with parent hierarchies (the parent's are not returned here).
     *
     * See the HierarchyGenerator which makes hierarchy's for you.
     *
     * @param Person $person
     *
     * @return Department[]
     */
    public function getTicketDepartmentsForPerson(Person $person)
    {
        return $this->generateAndCache(
            [
                'getAuthorizedDepartmentsForPersonInPortal',
                $person,
            ],
            [$this, 'fetchDepartments'],
            [$person, 'ticket']
        );
    }

    /**
     * An array of departments that are allowed for this person in chat.
     *
     * If a department that is returned has a parent, the calling code is expected to
     * deal with parent hierarchies (the parent's are not returned here).
     *
     * See the HierarchyGenerator which makes hierarchy's for you.
     *
     * @param Person $person
     *
     * @return Department[]
     */
    public function getChatDepartmentsForPerson(Person $person)
    {
        return $this->generateAndCache(
            [
                'getChatDepartmentsForPerson',
                $person,
            ],
            [$this, 'fetchDepartments'],
            [$person, 'chat']
        );
    }

    /**
     * An array of departments that are allowed for this person.
     *
     * If a department that is returned has a parent, the calling code is expected to
     * deal with parent hierarchies (the parent's are not returned here).
     *
     * See the HierarchyGenerator which makes hierarchy's for you.
     *
     * @param Person $person
     *
     * @return Department[]
     */
    public function getDepartmentsForPerson(Person $person)
    {
        return $this->generateAndCache(
            [
                'getDepartmentsForPerson',
                $person,
            ],
            [$this, 'fetchDepartments'],
            [$person]
        );
    }

    /**
     * @param Person $person
     * @param string $type
     *
     * @return array
     */
    public function fetchDepartments(Person $person, $type = '')
    {
        // department data service is used both for the portal and api
        // so we can't rely on portal permission bag and use legacy permission manager
        $allowedDepartmentIds = $person->getPermissionsManager()->Departments->getAllAllowed();
        $allowedDepartmentIds = array_merge(
            isset($allowedDepartmentIds['tickets']) ? array_keys($allowedDepartmentIds['tickets']) : [],
            isset($allowedDepartmentIds['chat']) ? array_keys($allowedDepartmentIds['chat']) : []
        );

        $qb = $this->em
            ->getRepository(Department::class)
            ->createQueryBuilder('d')
            ->select('d')
            ->where('d.id IN (:allowed_department_ids)')
            ->orderBy('d.display_order', 'ASC')
            ->setParameter('allowed_department_ids', $allowedDepartmentIds)
        ;

        switch ($type) {
            case 'chat':
                $qb->andWhere('d.is_chat_enabled = 1');
                break;
            case 'ticket':
                $qb->andWhere('d.is_tickets_enabled = 1');
                break;
            default:
                break;
        }

        $departments = $qb->getQuery()->getResult();

        return $departments;
    }
}

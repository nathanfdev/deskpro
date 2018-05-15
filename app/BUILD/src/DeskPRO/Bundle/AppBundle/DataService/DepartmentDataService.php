<?php

namespace DeskPRO\Bundle\AppBundle\DataService;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Usergroup;
use DeskPRO\Bundle\AppBundle\Security\Permissions\PermissionsManager;
use Doctrine\ORM\EntityManager;

/**
 * Class DepartmentDataService.
 */
class DepartmentDataService extends AbstractDataService
{
    /**
     * @var PermissionsManager
     */
    private $permissionsManager;

    /**
     * @var array
     */
    private $mightyUsers = [];

    /**
     * Constructor.
     *
     * @param EntityManager      $em
     * @param PermissionsManager $permissionsManager
     */
    public function __construct(EntityManager $em, PermissionsManager $permissionsManager)
    {
        parent::__construct($em);
        $this->permissionsManager = $permissionsManager;
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
     * @param Brand  $brand
     *
     * @return Department[]
     */
    public function getTicketDepartmentsForPerson(Person $person, Brand $brand = null)
    {
        return $this->generateAndCache(
            [
                'getAuthorizedDepartmentsForPersonInPortal',
                $person,
                $brand,
            ],
            [$this, 'fetchDepartments'],
            [$person, $brand, 'ticket']
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
     * @param Brand  $brand
     *
     * @return Department[]
     */
    public function getChatDepartmentsForPerson(Person $person, Brand $brand = null)
    {
        return $this->generateAndCache(
            [
                'getChatDepartmentsForPerson',
                $person,
                $brand,
            ],
            [$this, 'fetchDepartments'],
            [$person, $brand, 'chat']
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
     * @param Brand  $brand
     * @param string $type
     *
     * @return array
     */
    public function fetchDepartments(Person $person, Brand $brand = null, $type = '')
    {
        // department data service is used both for the portal and api
        // so we can't rely on portal permission bag and use legacy permission manager
        $person->loadHelper('PermissionsManager', ['force_load_usergroups' => true]);

        $allowedDepartments = $person->getPermissionsManager()->Departments->getAllAllowed();

        $chatDepartments   = isset($allowedDepartments['chat']) ? array_keys($allowedDepartments['chat']) : [];
        $ticketDepartments = isset($allowedDepartments['tickets']) ? array_keys($allowedDepartments['tickets']) : [];

        $qb = $this->em
            ->getRepository(Department::class)
            ->createQueryBuilder('d')
            ->select('d')
            ->where('d.id IN (:allowed_department_ids)')
            ->orderBy('d.display_order', 'ASC')
        ;

        if ($brand && $brand->getId()) {
            $qb->join('d.brands', 'b');
            $qb->andWhere('b.id = :brand');
            $qb->setParameter('brand', $brand);
        }

        switch ($type) {
            case 'chat':
                $qb->andWhere('d.is_chat_enabled = 1');
                $qb->setParameter('allowed_department_ids', $chatDepartments);
                break;
            case 'ticket':
                $qb->andWhere('d.is_tickets_enabled = 1');
                $qb->setParameter('allowed_department_ids', $ticketDepartments);
                break;
            default:
                $qb->setParameter('allowed_department_ids', array_merge($chatDepartments, $ticketDepartments));
                break;
        }

        $departments = $qb->getQuery()->getResult();

        return $departments;
    }

    /**
     * @param Department $department
     *
     * @return array
     */
    public function getDepartmentAgents(Department $department)
    {
        if (!$this->mightyUsers) {
            $mightyAgentGroups = $this->em->getRepository(Usergroup::class)->findBy([
                'sys_name' => [Usergroup::AGENT_ALL_PERM, Usergroup::AGENT_ALL_SAFE_PERM],
            ]);

            foreach ($mightyAgentGroups as $agentGroup) {
                foreach ($agentGroup->getPeople() as $agent) {
                    $this->mightyUsers[] = $agent->getId();
                }
            }
        }

        /** @var \Application\DeskPRO\EntityRepository\Department $departmentRepository */
        $departmentRepository = $this->em->getRepository(Department::class);
        $data                 = $departmentRepository->getPermissionsInfo($department);

        $ids = [];
        foreach ($data['agentgroups'] as $usergroup) {
            if ($usergroup['perm_name'] === 'full') {
                $ids[] = $usergroup['usergroup_id'];
            }
        }

        if ($ids) {
            $usergroups = implode(',', $ids);
            $sql        = "SELECT DISTINCT(person_id) FROM person2usergroups WHERE usergroup_id IN ({$usergroups})";
            $personIds  = $this->em->getConnection()->fetchAll($sql);
            $personIds  = array_column($personIds, 'person_id');
        } else {
            $personIds = [];
        }

        foreach ($personIds as &$id) {
            $id = (int) $id;
        }

        foreach ($data['agents'] as $agent) {
            if ($agent['perm_name'] === 'full') {
                $personIds[] = (int) $agent['agent_id'];
            }
        }

        return array_values(array_unique(array_merge($this->mightyUsers, $personIds), SORT_NUMERIC));
    }
}

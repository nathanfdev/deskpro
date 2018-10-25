<?php

namespace DeskPRO\Bundle\MessengerBundle\Service;

use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\EntityRepository\Person as PersonRepository;
use DeskPRO\Bundle\AppBundle\DataService\AgentDataService;
use DeskPRO\Bundle\AppBundle\Security\Permissions\PermissionsManager;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use Doctrine\ORM\EntityManager;

class TechService
{
    /**
     * @var PermissionsManager
     */
    private $permissionsManager;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * @var AgentDataService
     */
    private $agentDataService;

    /**
     * TechService constructor.
     *
     * @param EntityManager      $em
     * @param PermissionsManager $permissionsManager
     * @param BrandStack         $brandStack
     * @param AgentDataService   $agentDataService
     */
    public function __construct(
        EntityManager $em,
        PermissionsManager $permissionsManager,
        BrandStack $brandStack,
        AgentDataService $agentDataService
    ) {
        $this->permissionsManager = $permissionsManager;
        $this->em                 = $em;
        $this->brandStack         = $brandStack;
        $this->agentDataService   = $agentDataService;
    }

    /**
     * @return Department[]
     */
    public function getChatDepartments()
    {
        $permissionBag        = $this->permissionsManager->getPortalPermissionsBag();
        $allowedDepartmentIds = $permissionBag->getAllowedChatDepartmentIds();

        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('d')
            ->from(Department::class, 'd')
            ->join('d.brands', 'b')
            ->where(
                'd.is_chat_enabled = true',
                'd.id IN (:allowed_department_ids)',
                'b.id IN(:brand)'
            )
            ->setParameter('allowed_department_ids', $allowedDepartmentIds)
            ->setParameter('brand', $this->brandStack->getActive()->getBrand())
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Person[]
     */
    public function getAgentsOnline()
    {
        $agentIds = $this->agentDataService->getAgentsOnlineStatus();
        /** @var PersonRepository $personRepository */
        $personRepository = $this->em->getRepository(Person::class);

        return $personRepository->findBy(['id' => $agentIds['online']]);
    }
}

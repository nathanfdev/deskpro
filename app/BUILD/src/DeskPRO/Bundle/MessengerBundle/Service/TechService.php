<?php

namespace DeskPRO\Bundle\MessengerBundle\Service;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\EntityRepository\Person as PersonRepository;
use DeskPRO\Bundle\AppBundle\Security\Permissions\PermissionsManager;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
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
     * @var \DeskPRO\Bundle\BrandBundle\Brand\BrandStack
     */
    private $brandStack;

    /**
     * TechService constructor.
     *
     * @param EntityManager                                $em
     * @param PermissionsManager                           $permissionsManager
     * @param \DeskPRO\Bundle\BrandBundle\Brand\BrandStack $brandStack
     */
    public function __construct(
        EntityManager $em,
        PermissionsManager $permissionsManager,
        BrandStack $brandStack
    ) {
        $this->permissionsManager = $permissionsManager;
        $this->em                 = $em;
        $this->brandStack         = $brandStack;
    }

    /**
     * @return Department[]
     */
    public function getChatDepartments()
    {
        $permissionBag        = $this->permissionsManager->getPortalPermissionsBag();
        $allowedDepartmentIds = $permissionBag->getAllowedChatDepartmentIds();

        return $this->getDepartments('chat', $allowedDepartmentIds);
    }

    /**
     * @return Department[]
     */
    public function getTicketDepartments()
    {
        $permissionBag        = $this->permissionsManager->getPortalPermissionsBag();
        $allowedDepartmentIds = $permissionBag->getAllowedTicketDepartmentIds();

        return $this->getDepartments('tickets', $allowedDepartmentIds);
    }

    /**
     * @param string $type
     * @param array  $allowedDepartmentIds
     *
     * @return array
     */
    private function getDepartments($type, array $allowedDepartmentIds = [])
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('d')
            ->from(Department::class, 'd')
            ->leftJoin('d.brands', 'b')
            ->where(
                'd.is_'.$type.'_enabled = true',
                'd.id IN (:allowed_department_ids)',
                'd.parent IS NULL'
            )
            ->setParameter('allowed_department_ids', $allowedDepartmentIds)
        ;

        $self = $this;

        return array_filter($qb->getQuery()->getResult(),
            function ($dep) use ($self) {
                /* @var Department $dep */
                return !$dep->getBrands()->count() || $dep->hasBrand($self->brandStack->getActive()->getBrand());
            }
        );
    }

    /**
     * @return Person[]
     */
    public function getAgentsOnline()
    {
        /** @var PersonRepository $personRepository */
        $personRepository = $this->em->getRepository(Person::class);
        $agentIds         = $personRepository->getActiveAgentIdsForUserChat();

        return $personRepository->findBy(['id' => $agentIds]);
    }

    /**
     * @param $visitorId
     *
     * @return ChatConversation|null|object
     */
    public function getLastChatByVisitorId($visitorId)
    {
        $chatConversationRepo = $this->em->getRepository(ChatConversation::class);

        return $chatConversationRepo->findOneBy(['visitor_id' => $visitorId, 'status' => ChatConversation::STATUS_OPEN]);
    }
}

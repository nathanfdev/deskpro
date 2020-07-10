<?php

namespace DeskPRO\Bundle\MessengerBundle\Service;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\EntityRepository\Person as PersonRepository;
use DeskPRO\Bundle\AppBundle\Security\Permissions\PermissionsManager;
use DeskPRO\Bundle\AppBundle\Security\Permissions\Portal\PortalPermissionsManager;
use DeskPRO\Bundle\AppBundle\Security\Permissions\Portal\PortalUsergroupDecider;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use DeskPRO\Bundle\MessengerBundle\Common\TraitUserGet;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TechService
{
    use TraitUserGet;

    /**
     * @var PermissionsManager
     */
    private $permissionsManager;

    /**
     * @var PortalPermissionsManager
     */
    private $portalPermissionsManager;

    /**
     * @var PortalUsergroupDecider
     */
    private $usergroupDecider;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * TechService constructor.
     *
     * @param EntityManager            $em
     * @param PermissionsManager       $permissionsManager
     * @param PortalPermissionsManager $portalPermissionsManager
     * @param PortalUsergroupDecider   $usergroupDecider
     * @param BrandStack               $brandStack
     * @param ContainerInterface       $container
     */
    public function __construct(
        EntityManager $em,
        PermissionsManager $permissionsManager,
        PortalPermissionsManager $portalPermissionsManager,
        PortalUsergroupDecider $usergroupDecider,
        BrandStack $brandStack,
        ContainerInterface $container
    ) {
        $this->permissionsManager       = $permissionsManager;
        $this->portalPermissionsManager = $portalPermissionsManager;
        $this->usergroupDecider         = $usergroupDecider;
        $this->em                       = $em;
        $this->brandStack               = $brandStack;
        $this->container                = $container;
    }

    /**
     * @return Department[]
     */
    public function getChatDepartments()
    {
        $permissionBag        = $this->permissionsManager->getPortalPermissionsBag($this->getUserOrGuest());
        $allowedDepartmentIds = $permissionBag->getAllowedChatDepartmentIds();

        return $this->getDepartments('chat', $allowedDepartmentIds);
    }

    /**
     * @return Department[]
     */
    public function getTicketDepartments()
    {
        $permissionBag        = $this->permissionsManager->getPortalPermissionsBag($this->getUserOrGuest());
        $allowedDepartmentIds = $permissionBag->getAllowedTicketDepartmentIds();

        return $this->getDepartments('tickets', $allowedDepartmentIds);
    }

    public function getTicketPriorities()
    {
        return $this->container->get('data.ticket_built_in_fields')->getAll('priority');
    }

    public function getUsergroups()
    {
        $user = $this->getUser();

        return $user
            ? $this->usergroupDecider->getUsergroupIdsForPerson($user)
            : $this->usergroupDecider->getUsergroupIdsForGuest();
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
            ->setParameter('allowed_department_ids', $allowedDepartmentIds);

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

    public function canUseTickets()
    {
        $user = $this->getUser();

        $permissionsBag = $user
            ? $this->portalPermissionsManager->getPermissionsBagForPerson($user)
            : $this->portalPermissionsManager->getPermissionsBagForGuest();

        return $permissionsBag->get('tickets.use');
    }
}

<?php

namespace DeskPRO\Bundle\MessengerBundle\Serializer\Model;

use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Content\AvatarResolver;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Notifications\NotificationConfiguration;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;

/**
 * Class TechInfo.
 */
class TechInfo implements MessengerModelInterface
{
    /**
     * @var array
     */
    private $chatDepartments;

    /**
     * @var array
     */
    private $ticketDepartments;

    /**
     * @var array
     */
    private $ticketPriorities;

    /**
     * @var array
     */
    private $agentsOnline;

    /**
     * @var AvatarResolver
     */
    private $avatarResolver;

    /**
     * @var NotificationConfiguration
     */
    private $clientsSetup;

    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * @var boolean
     */
    private $canUseChat = false;

    /**
     * @var boolean
     */
    private $canUseTickets = false;

    /**
     * TechInfo constructor.
     *
     * @param AvatarResolver $avatarResolver
     * @param BrandStack     $brandStack
     */
    public function __construct(AvatarResolver $avatarResolver, BrandStack $brandStack)
    {
        $this->avatarResolver = $avatarResolver;
        $this->brandStack     = $brandStack;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $chatDepartments = [];
        foreach ($this->chatDepartments as $department) {
            $chatDepartments[] = $this->departmentToArray($department);
        }
        $ticketDepartments = [];
        foreach ($this->ticketDepartments as $department) {
            $ticketDepartments[] = $this->departmentToArray($department);
        }

        $self         = $this;
        $agentsOnline = array_map(function ($agent) use ($self) {
            /* @var Person $agent */
            $agentInfo = new AgentInfo($agent, $self->avatarResolver);

            return $agentInfo->toArray();
        }, $this->agentsOnline);

        $filter = function ($dep) {
            return !isset($dep['children']) || empty($dep['children']);
        };

        return [
            'canUseChat'         => $this->canUseChat,
            'canUseTickets'      => $this->canUseTickets,
            'chat_departments'   => array_values($chatDepartments),
            'ticket_departments' => array_values($ticketDepartments),
            'ticket_priorities'  => $this->ticketPriorities,
            'agents_online'      => $agentsOnline,
            'client'             => $this->clientsSetup->getClients()[0],
        ];
    }

    private function departmentToArray(Department $department)
    {
        $return = [
            'id'     => $department->getId(),
            'title'  => $department->getTitle(),
            'parent' => $department->getParentId() ?: null,
            'avatar' => $this->avatarResolver->getAvatarModel($department),
            'brands' => $department->getBrands()->map(function ($brand) {
                return $brand->getId();
            }),
        ];

        $self = $this;

        if ($department->getChildren()->count()) {
            $return['children'] = [];
            $children           = $department->getChildren()
                ->filter(
                    function ($department) use ($self) {
                        /* @var Department $department */
                        return $department->hasBrand($self->brandStack->getActive()->getBrand());
                    }
                )
                ->toArray();
            foreach ($children as $child) {
                $return['children'][] = $this->departmentToArray($child);
            }
        }

        return $return;
    }

    /**
     * @param array $agentsOnline
     *
     * @return $this
     */
    public function setAgentsOnline(array $agentsOnline)
    {
        $this->agentsOnline = $agentsOnline;

        return $this;
    }

    /**
     * @param array $chatDepartments
     *
     * @return $this
     */
    public function setChatDepartments(array $chatDepartments)
    {
        $this->chatDepartments = $chatDepartments;

        return $this;
    }

    /**
     * @param array $ticketDepartments
     *
     * @return $this
     */
    public function setTicketDepartments(array $ticketDepartments)
    {
        $this->ticketDepartments = $ticketDepartments;

        return $this;
    }

    /**
     * @param array $ticketPriorities
     *
     * @return $this
     */
    public function setTicketPriorities(array $ticketPriorities)
    {
        $this->ticketPriorities = $ticketPriorities;

        return $this;
    }

    /**
     * @param NotificationConfiguration $clientsSetup
     */
    public function setClientsSetup(NotificationConfiguration $clientsSetup)
    {
        $this->clientsSetup = $clientsSetup;
    }

    /**
     * @param bool $canUseChat
     *
     * @return $this
     */
    public function setCanUseChat($canUseChat)
    {
        $this->canUseChat = $canUseChat;

        return $this;
    }

    /**
     * @param bool $canUseTickets
     *
     * @return $this
     */
    public function setCanUseTickets($canUseTickets)
    {
        $this->canUseTickets = $canUseTickets;

        return $this;
    }
}

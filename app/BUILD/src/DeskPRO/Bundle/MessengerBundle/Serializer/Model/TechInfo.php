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
            $this->departmentToArray($department, $chatDepartments);
        }
        $ticketDepartments = [];
        foreach ($this->ticketDepartments as $department) {
            $this->departmentToArray($department, $ticketDepartments);
        }

        $self         = $this;
        $agentsOnline = array_map(function ($agent) use ($self) {
            /* @var Person $agent */
            return [
                'name'             => $agent->getDisplayNameUser(),
                'id'               => $agent->getId(),
                'avatar'           => $self->avatarResolver->getAvatar($agent),
                'chat_departments' => array_map('intval', $agent->getAllowedDepartments('chat')),
            ];
        }, $this->agentsOnline);

        return [
            'chat_departments'   => $chatDepartments,
            'ticket_departments' => $ticketDepartments,
            'agents_online'      => $agentsOnline,
            'client'             => $this->clientsSetup->getClients()[0],
        ];
    }

    private function departmentToArray(Department $department, &$departments)
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
            $children = $department->getChildren()
                ->filter(
                    function ($department) use ($self) {
                        /* @var Department $department */
                        return $department->hasBrand($self->brandStack->getActive()->getBrand());
                    }
                )
                ->toArray();
            $ids = [];
            foreach ($children as $child) {
                $this->departmentToArray($child, $departments);
                $ids[] = $child->getId();
            }
            if ($ids) {
                $return['children'] = $ids;
            }
        }

        $departments[] = $return;
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
     * @param NotificationConfiguration $clientsSetup
     */
    public function setClientsSetup(NotificationConfiguration $clientsSetup)
    {
        $this->clientsSetup = $clientsSetup;
    }
}

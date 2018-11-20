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
        $self            = $this;
        $chatDepartments = array_filter(array_map(function ($department) use ($self) {
            /* @var Department $department */
            return $self->departmentToArray($department);
        }, $this->chatDepartments), 'boolval');

        $ticketDepartments = array_filter(array_map(function ($department) use ($self) {
            /* @var Department $department */
            return array_filter($self->departmentToArray($department), 'boolval');
        }, $this->ticketDepartments), 'boolval');

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

    private function departmentToArray(Department $department)
    {
        $return = [
            'title'  => $department->getTitle(),
            'avatar' => $this->avatarResolver->getAvatar($department),
            'id'     => $department->getId(),
            ];
        $self = $this;
        if ($department->getChildren()->count()) {
            $children =
                array_values(array_map(
                    [$this, 'departmentToArray'],
                    $department->getChildren()
                        ->filter(
                            function ($department) use ($self) {
                                /* @var Department $department */
                                return $department->hasBrand($self->brandStack->getActive()->getBrand());
                            }
                        )
                        ->toArray()
                ));

            if ($children) {
                $return['children'] = $children;
            } else {
                $return = [];
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
     * @param NotificationConfiguration $clientsSetup
     */
    public function setClientsSetup(NotificationConfiguration $clientsSetup)
    {
        $this->clientsSetup = $clientsSetup;
    }
}

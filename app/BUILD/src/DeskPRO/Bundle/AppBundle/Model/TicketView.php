<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Model;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketAttachment;
use DeskPRO\Bundle\AppBundle\ObjectRouter\Configuration\PortalLinkCustom;
use DeskPRO\Bundle\AppBundle\ObjectRouter\Configuration\PortalLinkRoute;

/**
 * These mirror the link annotations from Application\DeskPRO\Entity\Ticket.
 *
 * We build this in the TicketViewDataService data service (it calcs all the $attributes for us, so we only do that once per request)
 *
 * @PortalLinkRoute("portal_tickets_guest_view", route_param_map={"auth":"auth"}, type="view_only")
 * @PortalLinkCustom()
 * @PortalLinkCustom(type="edit")
 * @PortalLinkCustom(type="resolve")
 * @PortalLinkCustom(type="unresolve")
 */
class TicketView
{
    /**
     * @var Ticket
     */
    protected $ticket;

    /**
     * @var TicketViewProperty[]
     */
    protected $properties = [];

    public function __construct(Ticket $ticket)
    {
        $this->properties = [];
        $this->ticket     = $ticket;
    }

    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * A proxy for the object router to use when generating links.
     *
     * @return string
     */
    public function getAuth()
    {
        return $this->ticket->getAuth();
    }

    public function hasAnyVisibleProperties()
    {
        foreach ($this->properties as $property) {
            if ($property->isVisible()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gets a ticket proprty by ID.
     *
     * The IDs are consts on this class, but also require a DB ID separated by _. For example, ticket_def_6.
     * IDs are generated consistently in the TicketDataViewService
     *
     * @param string $id the id of the property
     *
     * @return TicketViewProperty
     */
    public function getProperty($id)
    {
        if ($this->hasProperty($id)) {
            return $this->properties[$id];
        }
    }

    /**
     * @param int    $id
     * @param string $type
     * @param string $label
     * @param string $value
     * @param bool   $isAlwaysVisible
     * @param bool   $linkify
     */
    public function addProperty($id, $type, $label, $value, $isAlwaysVisible = false, $linkify = false)
    {
        $this->appendProperty(new TicketViewProperty($id, $type, $label, $value, $isAlwaysVisible, $linkify));
    }

    /**
     * @param TicketViewProperty $property
     *
     * @throws \Exception
     */
    public function appendProperty(TicketViewProperty $property)
    {
        if ($this->hasProperty($property->getId())) {
            throw new \InvalidArgumentException(
                sprintf(
                    'cannot add a TicketViewProperty to TicketView because a property with the ID "%s" already exists.',
                    $property->getId()
                )
            );
        }

        $this->properties[$property->getId()] = $property;
    }

    /**
     * @param mixed $id
     *
     * @return bool
     */
    public function hasProperty($id)
    {
        return array_key_exists($id, $this->properties);
    }

    /**
     * @return array|TicketViewProperty[]
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @return TicketAttachment[]
     */
    public function getAttachments()
    {
        return $this->ticket->getAttachments()->filter(function (TicketAttachment $attachment) {
            return !$attachment->isAgentNote() && $attachment->getMessage() && $attachment->getMessage()->getId();
        });
    }
}

<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\Model;

use DeskPRO\Bundle\AppBundle\ObjectRouter\Configuration\PortalLinkCustom;
use DeskPRO\Bundle\AppBundle\ObjectRouter\Configuration\PortalLinkRoute;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * These mirror the link annotations from Application\DeskPRO\Entity\Ticket.
 *
 * We build this in the TicketViewService data service (it calcs all the $attributes for us, so we only do that once per request)
 *
 * @PortalLinkRoute("portal_tickets_guest_view", route_param_map={"auth":"auth"}, type="view_only")
 * @PortalLinkCustom()
 * @PortalLinkCustom(type="edit")
 * @PortalLinkCustom(type="resolve")
 * @PortalLinkCustom(type="unresolve")
 */
class TicketView
{
    public $ticket;
    public $attribute_list = array();

    public function __call($name, $args)
    {
        return $this->__get($name);
    }

    public function __get($name)
    {
        if (isset($this->attribute_list[$name])) {
            return $this->attribute_list[$name];
        }
        if (isset($this->ticket->$name)) {
            return $this->ticket->$name;
        }

        // try an accesssor so twig will call the right function to get the value
        $accessor = PropertyAccess::createPropertyAccessor();
        try {
            return $accessor->getValue($this->ticket, $name);
        } catch (\Exception $e) {
            return;
        }
    }
}

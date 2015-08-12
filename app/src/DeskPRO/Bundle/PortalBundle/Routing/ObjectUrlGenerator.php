<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\PortalBundle\Routing;

use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use Application\DeskPRO\Entity\Ticket;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * This service understands how to generate a URL for a given object, and is used by a Twig extension
 * and by some services as well.
 *
 * The reason for this, is that some objects (a Ticket) require back-end logic from settings to determine what
 * the URL will actually be (ticket_ref, or id, as the identifier). So, this is a necessary service to keep things DRY.
 */
class ObjectUrlGenerator
{
    /**
     * @var UrlGeneratorInterface
     */
    private $url_generator;
    /**
     * @var BrandStack
     */
    private $brand_stack;

    public function __construct(UrlGeneratorInterface $url_generator, BrandStack $brand_stack)
    {
        $this->url_generator = $url_generator;
        $this->brand_stack = $brand_stack;
    }

    public function generatePortalUrl($object)
    {
        if ($object instanceof Ticket) {
            return $this->generateForTicket($object);
        }

        throw new \InvalidArgumentException('ObjectUrlGenerator does not know how to make a URL for ' . is_object($object) ? get_class($object) : 'scalar values');
    }

    protected function generateForTicket(Ticket $ticket)
    {
        if ($this->getSetting('core.tickets.use_ref')) {
            $ref = $ticket->getRef();
        } else {
            $ref = $ticket->getId();
        }

        return $this->url_generator->generate('portal_tickets_view', array('ticket_ref' => $ref));
    }

    protected function getSetting($name, $default = null)
    {
        return $this->brand_stack->getActive()->getSetting($name, $default);
    }
}

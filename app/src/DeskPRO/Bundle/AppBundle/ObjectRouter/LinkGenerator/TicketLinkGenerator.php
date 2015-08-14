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

namespace DeskPRO\Bundle\AppBundle\ObjectRouter\LinkGenerator;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Model\TicketView;
use DeskPRO\Bundle\AppBundle\ObjectRouter\LinkGeneratorInterface;
use DeskPRO\Bundle\AppBundle\ObjectRouter\ObjectRouter;
use DeskPRO\Bundle\AppBundle\ObjectRouter\ObjectRouterException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Tickets need special logic around the ticket_ref portion of it's portal route
 */
class TicketLinkGenerator implements LinkGeneratorInterface
{
    /**
     * @var UrlGeneratorInterface
     */
    private $url_generator;

    /**
     * @var SettingsResolver
     */
    private $settings_resolver;

    public function __construct(
        UrlGeneratorInterface $url_generator,
        SettingsResolver $settings_resolver
    )
    {
        $this->url_generator = $url_generator;
        $this->settings_resolver = $settings_resolver;
    }

    /**
     * Supports all TICKETs. We only get here if it's a CUSTOM link request.
     *
     * @param $object
     * @param $type
     * @param $context
     * @return bool
     */
    public function supports($object, $type, $context)
    {
        return $object instanceof Ticket || $object instanceof TicketView;
    }

    public function generate($ticket, $type, $context, $extra_params, $reference_type)
    {
        if ($ticket instanceof TicketView) {
            $ticket = $ticket->ticket;
        }

        /** @var \Application\DeskPRO\Entity\Ticket $ticket */
        if ($this->settings_resolver->getGlobalSettings()->get('core.tickets.use_ref')) {
            $ref = $ticket->getRef();
        } else {
            $ref = $ticket->getId();
        }

        if (ObjectRouter::CONTEXT_PORTAL !== $context) {
            throw new ObjectRouterException(
                'TicketLinkGenerator only supports PORTAL links, please implement the AGENT
                 context in DeskPRO\Bundle\AppBundle\ObjectRouter\LinkGenerator
            ');
        }

        if ($type === 'edit') {
            return $this->url_generator->generate(
                'portal_tickets_edit',
                array_merge(array('ticket_ref' => $ref), $extra_params),
                $reference_type
            );
        }


        return $this->url_generator->generate(
            'portal_tickets_view',
            array_merge(array('ticket_ref' => $ref), $extra_params),
            $reference_type
        );
    }
}

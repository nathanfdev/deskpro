<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\ObjectRouter\LinkGenerator;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Helper\TicketPublicIdResolver;
use DeskPRO\Bundle\AppBundle\Model\TicketView;
use DeskPRO\Bundle\AppBundle\ObjectRouter\LinkGeneratorInterface;
use DeskPRO\Bundle\AppBundle\ObjectRouter\ObjectRouter;
use DeskPRO\Bundle\AppBundle\ObjectRouter\ObjectRouterException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Tickets need special logic around the ticket_ref portion of it's portal route.
 */
class TicketLinkGenerator implements LinkGeneratorInterface
{
    /**
     * @var UrlGeneratorInterface
     */
    private $url_generator;

    /**
     * @var TicketPublicIdResolver
     */
    private $ticket_public_id_resolver;

    /**
     * Constructor.
     *
     * @param UrlGeneratorInterface  $url_generator
     * @param TicketPublicIdResolver $ticket_public_id_resolver
     */
    public function __construct(
        UrlGeneratorInterface  $url_generator,
        TicketPublicIdResolver $ticket_public_id_resolver
    ) {
        $this->url_generator             = $url_generator;
        $this->ticket_public_id_resolver = $ticket_public_id_resolver;
    }

    /**
     * Supports all TICKETs. We only get here if it's a CUSTOM link request.
     *
     * {@inheritdoc}
     */
    public function supports($object, $type, $context)
    {
        return $object instanceof Ticket || $object instanceof TicketView;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($ticket, $type, $context, $extra_params, $reference_type)
    {
        if ($ticket instanceof TicketView) {
            $ticket = $ticket->getTicket();
        }

        if (ObjectRouter::CONTEXT_PORTAL !== $context) {
            throw new ObjectRouterException(
                'TicketLinkGenerator only supports PORTAL links, please implement the AGENT
                 context in DeskPRO\Bundle\AppBundle\ObjectRouter\LinkGenerator
            ');
        }

        $ref = $this->ticket_public_id_resolver->findId($ticket);

        switch ($type) {
            case 'edit':
                return $this->url_generator->generate(
                    'portal_tickets_edit',
                    array_merge(['ticket_ref' => $ref], $extra_params),
                    $reference_type
                );
            case 'resolve':
                return $this->url_generator->generate(
                    'portal_tickets_resolve',
                    array_merge(['ticket_ref' => $ref], $extra_params),
                    $reference_type
                );
            case 'unresolve':
                return $this->url_generator->generate(
                    'portal_tickets_unresolve',
                    array_merge(['ticket_ref' => $ref], $extra_params),
                    $reference_type
                );
            case 'add-cc':
                return $this->url_generator->generate(
                    'portal_tickets_cc_add',
                    array_merge(['ticket_ref' => $ref], $extra_params),
                    $reference_type
                );
            default:
                return $this->url_generator->generate(
                    'portal_tickets_view',
                    array_merge(['ticket_ref' => $ref], $extra_params),
                    $reference_type
                );
        }
    }
}

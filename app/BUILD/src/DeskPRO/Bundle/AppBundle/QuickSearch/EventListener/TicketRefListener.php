<?php

namespace DeskPRO\Bundle\AppBundle\QuickSearch\EventListener;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchEvent;
use DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchEvents;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class TicketRefListener.
 */
class TicketRefListener implements EventSubscriberInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            QuickSearchEvents::SEARCH => 'onSearch',
        ];
    }

    /**
     * @param QuickSearchEvent $event
     */
    public function onSearch(QuickSearchEvent $event)
    {
        $context = $event->getContext();
        $request = $event->getRequest();

        if (!$context->isTicket() || !$request->isTicketRef()) {
            return;
        }

        $query = $request->getQuery();

        /** @var \Application\DeskPRO\EntityRepository\Ticket $repository */
        $repository = $this->em->getRepository(Ticket::class);
        $ticket     = $repository->findTicketRef($query);

        if ($ticket) {
            $context->addEntity($ticket);
        } elseif (strlen($query) >= 3) {
            $tickets = $repository->searchTicketRef($query);

            foreach ($tickets as $ticket) {
                $context->addEntity($ticket);
            }
        }
    }
}

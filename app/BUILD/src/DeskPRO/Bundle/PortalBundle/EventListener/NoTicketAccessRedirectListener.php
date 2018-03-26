<?php

namespace DeskPRO\Bundle\PortalBundle\EventListener;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\People\PersonGuest;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * If guest is trying to view own ticket (e.g. from email confirmation) then it's redirected to /register page
 * with pre-filled email and name form fields from the ticket person data.
 *
 * Class NoTicketAccessRedirectListener.
 */
class NoTicketAccessRedirectListener implements EventSubscriberInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * Constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     * @param EntityManager         $em
     * @param RouterInterface       $router
     */
    public function __construct(TokenStorageInterface $tokenStorage, EntityManager $em, RouterInterface $router)
    {
        $this->tokenStorage = $tokenStorage;
        $this->em           = $em;
        $this->router       = $router;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => ['onException', 150],
        ];
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $request   = $event->getRequest();

        if (!$exception instanceof AccessDeniedException
            || $request->attributes->get('_route') !== 'portal_tickets_view') {
            return;
        }

        // redirect allowed to guest users only
        $person = $this->tokenStorage->getToken()->getUser();
        if ($person instanceof Person && !$person instanceof PersonGuest) {
            return;
        }

        // get ticket
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('t')
            ->from(Ticket::class, 't')
            ->where('t.id = :ref OR t.ref = :ref')
            ->setParameter('ref', $request->attributes->get('ticket_ref'))
        ;

        /** @var Ticket $ticket */
        $ticket = $qb->getQuery()->getOneOrNullResult();
        if (!$ticket) {
            return;
        }

        // if ticket's user is already registered then skipping
        if ($ticket->getPerson()->isUser()) {
            return;
        }

        $event->setResponse(
            new RedirectResponse($this->router->generate(
                'portal_set_password',
                [
                    'email' => $ticket->getPerson()->getEmailAddress(),
                ],
                RouterInterface::ABSOLUTE_PATH
            )
        ));
    }
}

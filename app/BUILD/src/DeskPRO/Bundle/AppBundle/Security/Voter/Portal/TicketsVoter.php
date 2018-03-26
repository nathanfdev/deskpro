<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\Portal;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Security\Voter\AbstractVoter;
use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Can the user access tickets? Can the user edit or view a specific ticket?
 */
class TicketsVoter extends AbstractVoter
{
    const TICKET_LIST      = 'TICKET_LIST';
    const TICKET_VIEW      = 'TICKET_VIEW';
    const TICKET_VIEW_AUTH = 'TICKET_VIEW_AUTH';
    const TICKET_EDIT      = 'TICKET_EDIT';

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return $subject instanceof Ticket && in_array($attribute, [
            self::TICKET_LIST, self::TICKET_VIEW, self::TICKET_EDIT, self::TICKET_VIEW_AUTH,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $ticket, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$ticket instanceof Ticket) {
            throw new InvalidArgumentException('expected Ticket entity, but got "'.get_class($ticket).'"');
        }

        // none of the attributes currently supported by this voter will grant unauthenticated tokens
        if (!$this->isLoggedIn($user)) {
            return false;
        }

        $decision = false;

        switch ($attribute) {
            case static::TICKET_VIEW_AUTH:
                // view the ticket as a "guest" if you know the auth code
                $decision = $this->isLoggedIn($user);
                break;

            case static::TICKET_LIST:
                $decision = $this->isLoggedIn($user);
                break;

            case static::TICKET_VIEW:
                $decision = $ticket->isInvolved($user, 'user');
                break;

            case static::TICKET_EDIT:
                $decision =
                    $ticket->isInvolved($user, 'user')
                    && $ticket->isOwner($user)
                    && $ticket->hasVisibleStatus()
                ;
                break;
        }

        return $decision;
    }
}

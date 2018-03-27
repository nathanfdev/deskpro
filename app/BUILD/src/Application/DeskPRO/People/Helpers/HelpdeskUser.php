<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\People\Helpers;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\DeskPRO\EntityRepository\Ticket;
use Application\DeskPRO\HttpFoundation\Session;

/**
 * Helper added to People who are using the user interface.
 */
class HelpdeskUser extends \Application\DeskPRO\Domain\DomainObject implements \Orb\Helper\ShortCallableInterface
{
    /** @var \Application\DeskPRO\Entity\Person */
    protected $person;
    /** @var Session */
    protected $session;

    protected $ticket_count = null;

    public function __construct(Entity\Person $person, array $options)
    {
        $this->person  = $person;
        $this->session = $options['session'];
    }

    public function _getThis()
    {
        return $this;
    }

    public function getShortCallableNames()
    {
        return [
            'HelpdeskUser'    => '_getThis',
            'getHelpdeskUser' => '_getThis',
            'getTicketCount'  => 'getTicketCount',
        ];
    }

    public function getTicketCount()
    {
        if ($this->ticket_count !== null) {
            return $this->ticket_count;
        }

        /** @var Ticket $rep */
        $rep                = App::getEntityRepository('DeskPRO:Ticket');
        $this->ticket_count = $rep->countTicketsForPerson2($this->person);

        return $this->ticket_count;
    }

    /**
     * Check if the user has access to anything at all.
     *
     * @return bool
     */
    public function canDoAnything()
    {
        if ($this->person->hasPerm('tickets.use')) {
            return true;
        }

        if ($this->person->hasPerm('chat.use')) {
            return true;
        }

        if ($this->person->hasPerm('feedback.use')) {
            return true;
        }

        if ($this->person->hasPerm('articles.use')) {
            return true;
        }

        if ($this->person->hasPerm('downloads.use')) {
            return true;
        }

        if ($this->person->hasPerm('news.use')) {
            return true;
        }

        if ($this->person->hasPerm('guides.use')) {
            return true;
        }

        return false;
    }
}

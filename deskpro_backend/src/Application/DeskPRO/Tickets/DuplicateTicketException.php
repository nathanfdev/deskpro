<?php

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\App;
use Doctrine\ORM\EntityManager;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TicketFilter;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * This just looks at a filter and agents to determine who is able to use a filter,
 * and who is actually using it (based on prefs)
 */
class DuplicateTicketException extends \Exception
{
	public $ticket_id = null;
}

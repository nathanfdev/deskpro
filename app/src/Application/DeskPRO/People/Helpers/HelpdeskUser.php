<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\People\Helpers;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

/**
 * Helper added to People who are using the user interface
 */
class HelpdeskUser extends \Application\DeskPRO\Domain\DomainObject implements \Orb\Helper\ShortCallableInterface
{
	protected $person;
	protected $session;
	protected $visitor;

	protected $ticket_count = null;

	public function __construct(Entity\Person $person, array $options)
	{
		$this->person = $person;
		$this->session = $options['session'];
		$this->visitor = $options['visitor'];
	}

	public function _getThis()
	{
		return $this;
	}

	public function getShortCallableNames()
	{
		return array(
			'HelpdeskUser' => '_getThis',
			'getHelpdeskUser' => '_getThis',
			'getTicketCount' => 'getTicketCount',
		);
	}

	public function getTicketCount()
	{
		if ($this->ticket_count !== null) return $this->ticket_count;

		$this->ticket_count = App::getEntityRepository('DeskPRO:Ticket')->countTicketsForPerson($this->person);

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

		return false;
	}
}

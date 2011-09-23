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

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping as ORM_Mapping;

use Orb\Util\Arrays;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Tickets\TicketActions\ActionsFactory;
use Application\DeskPRO\Tickets\TicketActions\ActionsCollection;

/**
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\TicketSnippet")
 * @ORM_Mapping\Table(name="ticket_snippets")
 */
class TicketSnippet extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * Who created the snippet
	 *
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person")
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $person = null;

	/**
	 * @var \Application\DeskPRO\Entity\TicketCategory
	 * @ORM_Mapping\ManyToOne(targetEntity="TicketSnippetCategory", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="category_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $category;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="title", type="string", length=255)
	 */
	protected $title;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="snippet", type="text")
	 */
	protected $snippet;


	/**
	 * Format a snippet
	 *
	 * @param Application\DeskPRO\Entity\Ticket $ticket
	 * @param Application\DeskPRO\Entity\Person $person
	 * @return string
	 */
	public function snippetFormatted(Ticket $ticket = null, Person $person = null)
	{
		if ($ticket && !$person) {
			$person = $ticket->person;
		}

		$snippet = $this->snippet;

		// Basic replacements
		$repl = array(
			'time'          => date('h:ia'),
			'time24'        => date('H:i'),
			'date'          => date('F d, Y'),
			'my_name'       => App::getCurrentPerson()->getDisplayName(),
			'my_email'      => App::getCurrentPerson()->getPrimaryEmailAddress()
		);

		foreach ($repl as $k => $v) {
			$snippet = str_replace("{{ $k }}", $v, $snippet);
			$snippet = str_replace("{{{$k}}}", $v, $snippet);
		}

		// Go through properties on some objects

		$replace_from_object = function ($prefix, $obj) use (&$snippet) {
			$matches = null;
			if (preg_match_all('#\{\{\s*'.$prefix.'\.([a-zA-Z]{1}[a-zA-Z0-9_]+)\s*\}\}#', $snippet, $matches, PREG_SET_ORDER)) {
				foreach ($matches as $m) {
					$find = $m[0];
					$key = $m[1];

					if (isset($obj[$key])) {
						$snippet = str_replace($find, (string)$obj[$key], $snippet);
					}
				}
			}
		};

		if ($ticket) {
			$replace_from_object('ticket', $ticket);
			if ($ticket->agent) {
				$replace_from_object('agent', $ticket->agent);
			}
		}
		if ($person) {
			$replace_from_object('person', $person);
		}

		return $snippet;
	}

	/**
	 * Format a snippet for display as html (ie preview)
	 *
	 * @param Application\DeskPRO\Entity\Ticket $ticket
	 * @param Application\DeskPRO\Entity\Person $person
	 * @return string
	 */
	public function snippetFormattedHtml(Ticket $ticket = null, Person $person = null)
	{
		$snippet = $this->snippetFormatted($ticket, $person);
		$snippet = nl2br(htmlspecialchars(($snippet)));

		return $snippet;
	}
}

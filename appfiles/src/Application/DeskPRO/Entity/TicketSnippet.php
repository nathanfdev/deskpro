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

use \Orb\Util\Arrays;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Tickets\TicketActions\ActionsFactory;
use Application\DeskPRO\Tickets\TicketActions\ActionsCollection;

/**
 * Ticket macros
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\TicketSnippet")
 * @orm:Table(name="ticket_snippets")
 */
class TicketSnippet extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * Who created the snippet
	 * 
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $person = null;

	/**
	 * Teams who can use this snippet
	 * 
	 * @var Doctrine\Common\Collections\ArrayCollection
	 * @orm:ManyToMany(targetEntity="AgentTeam", cascade={"persist", "remove", "merge"})
     * @orm:JoinTable(name="ticket_snippet_to_team", joinColumns={@orm:JoinColumn(name="team_id", referencedColumnName="id", onDelete="cascade")}, inverseJoinColumns={@orm:JoinColumn(name="team_id", referencedColumnName="id", onDelete="cascade")})
	 */
	protected $agent_teams = null;

	/**
	 * Everyone can see it?
	 * 
	 * @var bool
	 * @orm:Column(name="is_global", type="boolean")
	 */
	protected $is_global = false;

	/**
	 * A plain-text category as string a string. We sort them properly when sending them
	 * to the client (so there's not a separate category table).
	 *
	 * @var string
	 * @orm:Column(name="category", type="string", length=255)
	 */
	protected $category;

	/**
	 * @var string
	 * @orm:Column(name="title", type="string", length=255)
	 */
	protected $title;

	/**
	 * @var string
	 * @orm:Column(name="snippet", type="text")
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
			if (preg_match_all('#\{\{\s*'.$prefix.'\.([a-zA-Z]{1}[a-zA-Z0-9]+)\s\}\}', $snippet, $matches, PREG_SET_ORDER)) {
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
}
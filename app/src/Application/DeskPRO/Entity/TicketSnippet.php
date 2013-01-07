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
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

use Orb\Util\Arrays;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Tickets\TicketActions\ActionsFactory;
use Application\DeskPRO\Tickets\TicketActions\ActionsCollection;

/**
 */
class TicketSnippet extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 */
	protected $id = null;

	/**
	 * Who created the snippet
	 *
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person = null;

	/**
	 * @var \Application\DeskPRO\Entity\TicketCategory
	 */
	protected $category;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $snippet = '';

	/**
	 * @var string
	 */
	protected $snippet_html = '';

	protected static $_replacement_cache = array();

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}


	/**
	 * Format a snippet
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 * @param \Application\DeskPRO\Entity\Person $person
	 * @param array|bool $pattern
	 * @param string|bool $snippet If specified, uses this snippet as an override
	 *
	 * @return string
	 */
	public function snippetFormatted(Ticket $ticket = null, Person $person = null, $pattern = false, $snippet = false)
	{
		if (!is_array($pattern) && $pattern) {
			// passed true - render as html with no wrapping
			$pattern = array('', '', true);
		} else if (!$pattern) {
			// 0=>wrapstart, 1=>wrapend, 2=>render as html
			$pattern = array('', '', false);
		}

		if ($snippet === false) {
			if ($pattern[2]) {
				$snippet = $this->getSnippetHtml();
			} else {
				$snippet = $this->snippet;
			}
		}

		$current_person = App::getCurrentPerson();
		$cache_key = ($ticket ? $ticket->id : '') . '-' . $current_person->id;

		if (!isset(self::$_replacement_cache[$cache_key])) {
			$repl = array(
				'var.time'          => date('h:ia'),
				'var.time24'        => date('H:i'),
				'var.date'          => date('F d, Y'),
				'me.name'       => $current_person->getDisplayName(),
				'me.email'      => $current_person->getPrimaryEmailAddress(),
			);

			// Custom user fields for current agent: {{ me.field23 }}
			$field_manager = App::getSystemService('person_fields_manager');
			$custom_fields = $field_manager->getRenderedToTextForObject($current_person);
			foreach ($custom_fields as $f) {
				$repl["me.field{$f['id']}"] = $f['rendered'];
			}

			if ($person) {
				$repl = array_merge(array(
					'user.name'                   => $person->getDisplayName(),
					'user.email'                  => $person->getPrimaryEmailAddress(),
					'user.organization_position'  => $person->organization_position,

					'org.name' => $person->organization ? $person->organization->name : '',
				), $repl);

				// Custom user fields: {{ user.field23 }}
				$field_manager = App::getSystemService('person_fields_manager');
				$custom_fields = $field_manager->getRenderedToTextForObject($person);
				foreach ($custom_fields as $f) {
					$repl["user.field{$f['id']}"] = $f['rendered'];
				}

				// Custom org fields: {{ agent.field23 }}
				if ($person->organization) {
					$field_manager = App::getSystemService('org_fields_manager');
					$custom_fields = $field_manager->getRenderedToTextForObject($person->organization);
					foreach ($custom_fields as $f) {
						$repl["org.field{$f['id']}"] = $f['rendered'];
					}
				}
			}

			// If we dont have a ticket, then no replacements
			if ($ticket) {
				if ($ticket && !$person) {
					$person = $ticket->person;
				}

				// Basic replacements
				$repl = array_merge(array(
					'ticket.id'               => $ticket->id,
					'ticket.ref'              => $ticket->ref,
					'ticket.subject'          => $ticket->subject,
					'ticket.department'       => $ticket->department ? $ticket->department->full_title : '',
					'ticket.product'          => $ticket->product ? $ticket->product->full_title : '',
					'ticket.category'         => $ticket->category ? $ticket->category->full_title : '',
					'ticket.workflow'         => $ticket->workflow ? $ticket->workflow->title : '',
					'ticket.priority'         => $ticket->priority ? $ticket->priority->title : '',
					'ticket.date_created'     => date('F d, Y', $ticket->date_created->getTimestamp()),
					'ticket.time_created'     => date('h:ia', $ticket->date_created->getTimestamp()),
					'ticket.date_closed'      => $ticket->date_closed ? date('F d, Y', $ticket->date_closed->getTimestamp()) : '',
					'ticket.time_closed'      => $ticket->date_closed ? date('h:ia', $ticket->date_closed->getTimestamp()) : '',
					'ticket.date_resolved'    => $ticket->date_resolved ? date('F d, Y', $ticket->date_resolved->getTimestamp()) : '',
					'ticket.time_resolved'    => $ticket->date_resolved ? date('h:ia', $ticket->date_resolved->getTimestamp()) : '',

					'agent.name'     => $ticket->agent ? $ticket->agent->getDisplayName() : '',
					'agent.email'    => $ticket->agent ? $ticket->agent->getPrimaryEmailAddress() : '',

					'agent_team.name' => $ticket->agent_team ? $ticket->agent_team->name : '',
				), $repl);

				// Custom ticket fields: {{ ticket.field23 }}
				$field_manager = App::getSystemService('ticket_fields_manager');
				$custom_fields = $field_manager->getRenderedToTextForObject($ticket);
				foreach ($custom_fields as $f) {
					$repl["ticket.field{$f['id']}"] = $f['rendered'];
				}

				// Custom user fields for assigned agent: {{ agent.field23 }}
				if ($ticket->agent) {
					$field_manager = App::getSystemService('person_fields_manager');
					$custom_fields = $field_manager->getRenderedToTextForObject($ticket->agent);
					foreach ($custom_fields as $f) {
						$repl["agent.field{$f['id']}"] = $f['rendered'];
					}
				}
			}

			self::$_replacement_cache[$cache_key] = $repl;
		} else {
			$repl = self::$_replacement_cache[$cache_key];
		}

		$snippet = preg_replace_callback('/\{\{\s*([a-z0-9_.-]+)\s*\}\}/i', function($match) use ($repl, $pattern) {
			$k = $match[1];
			if (isset($repl[$k])) {
				$v = $repl[$k];
				if ($pattern[2]) {
					$v = htmlspecialchars($v);
				}

				return $pattern[0] . $v . $pattern[1];
			} else {
				return $match[0];
			}
		}, $snippet);

		// Replace anything remaining with blanks,
		//$snippet = preg_replace('#\{\{[ ]?(var|me|agent|agent_team|user|org|ticket)\.([a-zA-Z0-9_]+)[ ]?\}\}#', '', $snippet);

		return $snippet;
	}

	/**
	 * Format a snippet for display as an html preview
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 * @param \Application\DeskPRO\Entity\Person $person
	 *
	 * @return string
	 */
	public function snippetFormattedHtmlPreview(Ticket $ticket = null, Person $person = null)
	{
		$snippet = $this->snippetFormatted(
			$ticket,
			$person,
			array('<span class="replacement">', '</span>', true),
			$this->snippet_html ? $this->snippet_html : nl2br(htmlspecialchars($this->snippet))
		);

		return $snippet;
	}

	/**
	 * Format a snippet for display as html
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 * @param \Application\DeskPRO\Entity\Person $person
	 *
	 * @return string
	 */
	public function snippetFormattedHtml(Ticket $ticket = null, Person $person = null)
	{
		$snippet = $this->snippetFormatted(
			$ticket,
			$person,
			true,
			$this->getSnippetHtml()
		);

		return $snippet;
	}

	public function setSnippet($snippet)
	{
		$snippet = trim($snippet);

		$this->setModelField('snippet', $snippet);
		$this->setModelField('snippet_html', nl2br(htmlspecialchars($snippet)));
	}

	public function setSnippetHtml($snippet)
	{
		$snippet = \Orb\Util\Strings::trimHtml($snippet);

		$this->setModelField('snippet_html', $snippet);
		$this->setModelField('snippet', \Orb\Util\Strings::convertWysiwygHtmlToText($snippet));
	}

	public function getSnippetHtml()
	{
		if (!$this->snippet_html) {
			return nl2br(htmlspecialchars($this->snippet));
		}

		return $this->snippet_html;
	}



	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TicketSnippet';
		$metadata->setPrimaryTable(array( 'name' => 'ticket_snippets', ));
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
		$metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'id', 'id' => true, ));
		$metadata->mapField(array( 'fieldName' => 'title', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'title', ));
		$metadata->mapField(array( 'fieldName' => 'snippet', 'type' => 'text', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'snippet', ));
		$metadata->mapField(array( 'fieldName' => 'snippet_html', 'type' => 'text', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'snippet_html', ));
		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
		$metadata->mapManyToOne(array( 'fieldName' => 'person', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Person', 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'person_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => 'set null', 'columnDefinition' => NULL, ), ),  ));
		$metadata->mapManyToOne(array( 'fieldName' => 'category', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TicketSnippetCategory', 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'category_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => 'cascade', 'columnDefinition' => NULL, ), ),  ));
	}
}

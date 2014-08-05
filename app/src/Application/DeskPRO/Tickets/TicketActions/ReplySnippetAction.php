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
 * @subpackage Tickets
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\People\PersonContextInterface;
use Application\DeskPRO\Tickets\SnippetFormatter;

class ReplySnippetAction extends AbstractAction implements PersonContextInterface, PermissionableAction
{
	/**
	 * @var ReplySnippetActionItem[]
	 */
	protected $snippet_items;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person_context;

	public function __construct($snippet_id, $reply_pos = null)
	{
		$item = new ReplySnippetActionItem();
		$item->snippet_id = $snippet_id;
		$item->reply_pos  = $reply_pos;
		$item->snippet = App::getOrm()->find('DeskPRO:TextSnippet', $snippet_id);

		if ($item->snippet) {
			$this->addSnippetItem($item);
		}
	}


	/**
	 * @param ReplySnippetActionItem $item
	 */
	public function addSnippetItem(ReplySnippetActionItem $item)
	{
		$this->snippet_items[] = $item;
	}


	/**
	 * @return ReplySnippetActionItem[]
	 */
	public function getSnippetItems()
	{
		return $this->snippet_items;
	}


	/**
	 * @return string[]
	 */
	public function getSnippetTitles()
	{
		$titles = array();
		foreach ($this->snippet_items as $item) {
			$t = $item->snippet ? $item->snippet->title : null;
			if ($t) {
				$titles[] = $t;
			}
		}

		return $titles;
	}


	/**
	 * @return int[]
	 */
	public function getSnippetIds()
	{
		$ids = array();
		foreach ($this->snippet_items as $item) {
			$id = $item->snippet ? $item->snippet->id : null;
			if ($id) {
				$ids[] = $id;
			}
		}

		return $ids;
	}


	/**
	 * @param \Application\DeskPRO\Entity\Person $person
	 * @return void
	 */
	public function setPersonContext(Person $person)
	{
		$this->person_context = $person;
	}


	public function checkPermission(Ticket $ticket, Person $person)
	{
		if (!$person->PermissionsManager->TicketChecker->canReply($ticket)) {
			return false;
		}

		return true;
	}

	/**
	 * Apply the property to the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function apply(Ticket $ticket)
	{
		if (!$this->snippet_items) {
			return;
		}

		if (!$this->person_context) {
			if ($ticket->agent) {
				$this->person_context = $ticket->agent;
			} else {
				// Try to find last agent to replied in tikcet
				$agent_id = App::getDb()->fetchColumn("
					SELECT tickets_messages.person_id
					FROM tickets_messages
					LEFT JOIN people ON (people.id = tickets_messages.person_id)
					WHERE tickets_messages.ticket_id = 1 AND people.is_agent = 1
					ORDER BY tickets_messages.id DESC
				");

				if ($agent_id) {
					$this->person_context = App::getDataService('Agent')->get($agent_id);
				}
			}
		}

		if (!$this->person_context) {
			return;
		}

		$message = new TicketMessage();
		$message->person = $this->person_context;

		$snippet_text = array();
		foreach ($this->snippet_items as $item) {
			$text = App::getTranslator()->objectChoosePhraseText(
				$item->snippet,
				'snippet',
				array(
					$ticket->language,
					$this->person_context->getRealLanguage()
				)
			);
			$text = trim($text);
			if (!$text) {
				continue;
			}

			switch ($item->reply_pos) {
				case 'append':
					$snippet_text[] = $text;
					break;
				case 'prepend':
					array_unshift($snippet_text, $text);
					break;
				case 'overwrite':
					$snippet_text = array($text);
					break;
			}
		}

		$snippet_text = implode("\n<br/><br/>\n", $snippet_text);

		$formatter = new SnippetFormatter(App::getContainer()->get('twig'));

		$this->person_context->loadHelper('Agent');
		$formatter->addVar('agent_signature', $this->person_context->getSignatureHtml());

		$message->setMessageHtml($formatter->formatText($snippet_text, $ticket));
		$ticket->addMessage($message);
	}


	/**
	 * Get an array of actions that would be performed on the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function getApplyActions(Ticket $ticket)
	{
		if (!$this->snippet_items) {
			return array();
		}

		return array(
			array('action' => 'reply_snippet')
		);
	}


	/**
	 * @param \Application\DeskPRO\Tickets\TicketActions\ActionInterface $other_action
	 * @return \Application\DeskPRO\Tickets\TicketActions\ActionInterface
	 */
	public function merge(ActionInterface $other_action)
	{
		foreach ($other_action->getSnippetItems() as $item) {
			$this->addSnippetItem($item);
		}

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDescription($as_html = true)
	{
		if (!$this->snippet_items) {
			return "<error>No snippet</error>";
		}

		// Hack ot append the proper position
		// when being viewed from replybox
		// See TicketController::ajaxGetMacroAction
		if (isset($_GET['macro_reply_context'])) {
			$ret = array();
			$reply_pos = null;

			foreach ($this->snippet_items as $item) {
				if (!$reply_pos) {
					$reply_pos = $item->reply_pos;
				}
				if ($item->reply_pos == 'overwrite') {
					$ret[] = "Reply with snippet: " . $item->snippet->title;
				} elseif ($item->reply_pos == 'append') {
					$ret[] = "Append snippet to reply: " . $item->snippet->title;
				} else {
					$ret[] ="Prepend snippet to reply: " . $item->snippet->title;
				}
			}

			$ret = implode(', ', $ret);

			$html = '';
			if (!empty($GLOBALS['DP_ACTIVE_TICKET'])) {

				$snippet_text = array();
				foreach ($this->snippet_items as $item) {
					$text = App::getTranslator()->objectChoosePhraseText(
						$item->snippet,
						'snippet',
						array(
							$GLOBALS['DP_ACTIVE_TICKET']->language
						)
					);
					$text = trim($text);
					if (!$text) {
						continue;
					}

					switch ($item->reply_pos) {
						case 'append':
							$snippet_text[] = $text;
							break;
						case 'prepend':
							array_unshift($snippet_text, $text);
							break;
						case 'overwrite':
							$snippet_text = array($text);
							break;
					}
				}

				$snippet_text = implode("\n<br/><br/>\n", $snippet_text);
				$formatter = new SnippetFormatter(App::getContainer()->get('twig'));
				$formatter->addVar('agent_signature', '');
				$html = $formatter->formatText($snippet_text, $GLOBALS['DP_ACTIVE_TICKET']);
			}

			$ret = '<span class="with-reply" data-reply-pos="' . $reply_pos . '">' . $ret . '<script type="text/x-deskpro-plain" class="reply-text">' . $html . '</script></span>';
			return $ret;
		}

		return "Reply with snippet: " . implode(', ', $this->getSnippetTitles());
	}
}

class ReplySnippetActionItem
{
	/**
	 * @var \Application\DeskPRO\Entity\TextSnippet
	 */
	public $snippet;

	/**
	 * @var int
	 */
	public $snippet_id;

	/**
	 * Possible values: append, prepend, overwrite
	 * @var string
	 */
	public $reply_pos;
}
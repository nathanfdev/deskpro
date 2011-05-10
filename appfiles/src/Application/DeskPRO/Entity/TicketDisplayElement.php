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

use \Application\DeskPRO\App;

use Orb\Util\Strings;
use Orb\Util\Arrays;

use \Application\DeskPRO\Entity;

/**
 * Ticket-related display information
 *
 * @orm:Entity
 * @orm:Table(name="ticket_display_elements")
 */
class TicketDisplayElement extends \Application\DeskPRO\Domain\DomainObject
{
	const ZONE_AGENT = 'agent';
	const ZONE_USER  = 'user';

	const ELEMENT_FIELD = 'field';
	const ELEMENT_WIDGET = 'widget';

	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\Department
	 * @orm:ManyToOne(targetEntity="Department")
	 * @orm:JoinColumn(name="department_id", referencedColumnName="id")
	 */
	protected $department = null;

	/**
	 * Where this display field description applies: user, agent
	 *
	 * @var string
	 * @orm:Column(name="element_type", type="string", length=50)
	 */
	protected $display_zone;

	/**
	 * The type of elemenet: ticket_field, widget
	 *
	 * @var string
	 * @orm:Column(name="element_type", type="string", length=50)
	 */
	protected $element_type;

	/**
	 * The ID of the element
	 *
	 * @var string
	 * @orm:Column(name="element_id", type="integer")
	 */
	protected $element_id = 0;

	/**
	 * The initial state of the element. When the conditions fail,
	 * this state is reversed.
	 *
	 * @var string
	 * @orm:Column(name="initial_state", type="string", length=50)
	 */
	protected $initial_state = 'visible';

	/**
	 * An array of checks to run to see if the element should display.
	 * All of these must match.
	 *
	 * @var array
	 * @orm:Column(name="conds_all", type="array")
	 */
	protected $conds_all = array();

	/**
	 * An array of checks to run to see if the element should display.
	 * Any one of these must match.
	 *
	 * @var array
	 * @orm:Column(name="conds_any", type="array")
	 */
	protected $conds_any = array();


	/**
	 * @var int
	 * @orm:Column(name="display_order", type="integer")
	 */
	protected $display_order = 0;

	public function getDepartmentId()
	{
		if ($this->department) {
			return $this->department['id'];
		}

		return 0;
	}


	/**
	 * Check a ticket against this rule to see if it matches
	 *
	 * @param  $ticket
	 * @return bool
	 */
	public function isTicketMatch(Ticket $ticket)
	{
		$cond = App::getEntityRepository('DeskPRO:Department')->getIdsInTree($this->department['id'], true);
		if (!Arrays::isIn($ticket['department_id'], $cond)) {
			return false;
		}

		if ($this->conds_all) {
			$ticket_terms = new \Application\DeskPRO\Tickets\TicketTerms($this->conds_all);
			if (!$ticket_terms->doesTicketMatch($ticket)) {
				return false;
			}
		}

		if ($this->conds_any) {
			$ticket_terms = new \Application\DeskPRO\Tickets\TicketTerms($this->conds_any);
			if (!$ticket_terms->doesTicketMatchAny($ticket)) {
				return false;
			}
		}

		return true;
	}



	/**
	 * 'Compile' this rule into a javascript function that can be applied form the client.
	 *
	 * @return string
	 */
	public function compileToJavascript()
	{
		$js = array();
		$js[] = "function (t) {";

		if ($this->conds_all OR $this->conds_any) {
			if ($this->conds_all) {
				$ticket_terms = new \Application\DeskPRO\Tickets\TicketTerms($this->conds_all);
				$js[] = "var all_check = function(ticket) {";
				$js[] = $ticket_terms->compileTermsToJavascript('all');
				$js[] = "};";
			} else {
				$js[] = "var all_check = function(ticket){return true;};";
			}

			if ($this->conds_any) {
				$ticket_terms = new \Application\DeskPRO\Tickets\TicketTerms($this->conds_any);
				$js[] = "var any_check = function(ticket) {";
				$js[] = $ticket_terms->compileTermsToJavascript('any');
				$js[] = "};";
			} else {
				$js[] = "var any_check = function(ticket){return true;};";
			}

			$js[] = "if (any_check(t) && all_check(t)) return true; else return false;";
		} else {
			$js[] = 'return true';
		}

		$js[] = "}";

		$js = implode(' ', $js);

		// Very simple minify
		$js = str_replace("\n", ' ', $js);
		$js = preg_replace("# {2,}#", ' ', $js);
		$js = preg_replace("#;\w+#", ';', $js);
		$js = str_replace('if (', 'if(', $js);
		$js = str_replace('ticket.', 't.', $js);
		$js = str_replace('function(ticket)', 'function(t)', $js);

		return $js;
	}
}
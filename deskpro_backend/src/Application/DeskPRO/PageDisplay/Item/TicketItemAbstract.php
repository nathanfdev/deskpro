<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage PageDisplay
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\PageDisplay\Item;

use Application\DeskPRO\PageDisplay\Item\ItemInterface;

abstract class TicketItemAbstract extends ItemAbstract
{
	public function compileJsCheck()
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
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

namespace Application\DeskPRO\PageDisplay\Page;

use Application\DeskPRO\App;

use Application\DeskPRO\People\PersonContextInterface;

use Application\DeskPRO\Entity\PageDisplayAbstract;
use Application\DeskPRO\Entity\TicketPageDisplay;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Department;

/**
 * TicketPage's have a zone context (user, agent) and a department context.
 *
 * The person context is used in criteria for rule matching only.
 */
class TicketPageZone extends BasicPage implements PersonContextInterface
{
	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person_context;

	/**
	 * The department context for all these pagedisplays
	 * @var \Application\DeskPRO\Entity\Department
	 */
	protected $department;

	/**
	 * The zone we're in
	 */
	protected $zone;

	/**
	 * @param string $zone The zone (one of TicketPageDisplay::ZONE_*)
	 * @param Department $department The department context
	 */
	public function __construct($zone, Department $department)
	{
		$this->zone = $zone;
		$this->department = $department;
	}

	
	/**
	 * @param \Application\DeskPRO\Entity\Person $person
	 * @return void
	 */
	public function setPersonContext(Person $person)
	{
		$this->person_context = $person;
	}


	/**
	 * @param \Application\DeskPRO\Entity\PageDisplayAbstract $page_display
	 * @return void
	 */
	public function addPageDisplay(PageDisplayAbstract $page_display)
	{
		if (!($page_display instanceof TicketPageDisplay)) {
			throw new \InvalidArgumentException('You can only add `TicketPageDisplay` types');
		}

		if ($this->zone != $page_display['zone']) {
			throw new \InvalidArgumentException('Invalid zone context. Must be: ' . $this->zone);
		}
		if ($this->department['id'] != $page_display->department['id']) {
			throw new \InvalidArgumentException('Invalid department context. Must be: ' . $this->department['id']);
		}

		parent::addPageDisplay($page_display);
	}


	/**
	 * Reads page displays from the TicketPageDisplay entity repository
	 * @return void
	 */
	public function addPageDisplaysFromDb()
	{
		$page_displays = App::getEntityRepository('DeskPRO:TicketPageDisplay')->getFromZone($this->zone, $this->department);
		$this->addPageDisplays($page_displays);
	}

	
	/**
	 * Get the zone
	 * 
	 * @return string
	 */
	public function getZone()
	{
		return $this->zone;
	}
	

	/**
	 * Get the department
	 * 
	 * @return \Application\DeskPRO\Entity\Department
	 */
	public function getDepartment()
	{
		return $this->department;
	}


	public function compileJs()
	{
		$part = array();

		foreach ($this->page_displays as $ticket_page) {
			/** @var $ticket_page \Application\DeskPRO\Entity\TicketPageDisplay */
			$page_part = $this->compileTicketPage($ticket_page);

			if ($page_part) {
				$part[] = $page_part;
			}
		}

		return implode(",\n", $part);
	}

	public function compileTicketPage($ticket_page)
	{
		$parts = array();
		$function_tokens = array();

		foreach ($ticket_page['data'] as $item) {
			if ($item['item_type'] == 'group') {
				if (empty($item['items'])) {
					continue;
				}

				$sub_item_parts = array();
				foreach ($item['items'] as $sub_item) {
					$sub_item_parts[] = $this->_compileArrayForItem($ticket_page, $sub_item, $function_tokens);
				}

				$parts[] = array('section' => $ticket_page['section'], 'item_type' => 'group', 'title' => $item['title'], 'items' => $sub_item_parts);
			} else {
				$parts[] = $this->_compileArrayForItem($ticket_page, $item, $function_tokens);
			}
		}

		if (!$parts) {
			return false;
		}

		$parts = json_encode($parts);

		foreach ($function_tokens as $token => $function) {
			$parts = str_replace("\"$token\"", $function, $parts);
		}
		
		return $parts;
	}

	protected function _compileArrayForItem($ticket_page, $item, array &$function_tokens)
	{
		$part = array();
		$part['section'] = $ticket_page['section'];
		$part['item_type'] = $item['item_type'];

		if (isset($item['item_id'])) {
			$part['item_id'] = $item['item_id'];
		}

		$check = array('ticket_categories', 'ticket_workflows', 'ticket_priorities', 'ticket_products');
		foreach ($check as $k) {
			if (isset($item[$k])) {
				$part[$k] = $item[$k];
			}
		}

		$part['initial_display'] = 'visible';
		if (!empty($item['initial_display'])) {
			$part['initial_display'] = $item['initial_display'];
		}

		if (empty($item['terms_all']) && empty($item['terms_any'])) {
			$part['check'] = false;
		} else {
			$token = '%' . microtime(true) . mt_rand(1000, 9999) . mt_rand(1000, 9999) . '%';
			$function = $this->_compileFunctionCheck($item);

			$function_tokens[$token] = $function;

			$part['check'] = $token;
		}

		return $part;
	}

	protected function _compileFunctionCheck($item)
	{
		$function = array();
		$function[] = 'function (ticket) {';
		if (!empty($item['terms_all'])) {
			$terms_compiler = new \Application\DeskPRO\Tickets\TicketTerms($item['terms_all']);
			$terms_checks = $terms_compiler->compileTermsToJavascript('all');

			$function[] = 'var all = (function(){' . $terms_checks. '})();';
		} else {
			$function[] = 'var all = true;';
		}
		if (!empty($item['terms_any'])) {
			$terms_compiler = new \Application\DeskPRO\Tickets\TicketTerms($item['terms_any']);
			$terms_checks = $terms_compiler->compileTermsToJavascript('any');

			$function[] = 'var any = (function(){' . $terms_checks. '})();';
		} else {
			$function[] = 'var any = true;';
		}

		$function[] = "if (all && any) return true;";
		$function[] = "else (all && any) return false;";

		$function[] = '}';

		return implode(' ', $function);
	}
}
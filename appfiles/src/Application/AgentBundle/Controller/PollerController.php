<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage TechBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\TechBundle\Controller;

use \Orb\Util\Strings;

use Application\DeskPRO\App;

/**
 * Handles creating/editing of API keys
 */
class PollerController extends AbstractController
{
	############################################################################
	# /tech/poller                                         tech_interface_poller
	############################################################################

	/**
	 * Handle a poller request
	 */
	public function handlerAction()
	{
		$dos = $this->in->getArrayValue('do');

		$data = array();

		foreach ($dos as $do) {
			$do = Strings::dashToCamelCase($do);
			$method = $do . 'Message';
			$data[] = $this->$method();
		}

		return $this->createJsonResponse(json_encode(array(
			'messages' => $data
		)));
	}


	############################################################################
	# getFilterCounts
	############################################################################

	public function getFilterCountsMessage()
	{
		$all_counts = $queues = App::getApi('tickets.queues')->getAllCountsForPersonQueues($this->person);

		return array('filters.counts', $all_counts);
	}
}
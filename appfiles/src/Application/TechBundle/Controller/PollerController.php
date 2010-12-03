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

		return $this->createJsonResponse(array(
			'messages' => $data
		));
	}


	############################################################################
	# getFilterCounts
	############################################################################

	public function getFilterCountsMessage()
	{
		$filters = $this->em->createQuery("
			SELECT q
			FROM CoreBundle:TicketQueue q
			WHERE q.person_id = ?1 OR q.is_global = true
		")->setParameter(1, $this->person['id'])->execute();

		$all_counts = array();

		foreach ($filters as $filter) {
			$searcher = $filter->getSearcher();
			$searcher->enableArchiveSearch();//todo
			$count = count($searcher->getMatches());

			$all_counts[$filter['id']] = $count;
		}

		return array('filters.counts', $all_counts);
	}
}
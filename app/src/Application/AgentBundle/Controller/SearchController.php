<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */
namespace Application\AgentBundle\Controller;

use Application\DeskPRO\App;
use Orb\Util\Numbers;
use Application\DeskPRO\Search\SearcherResult\ResultSet;
use Application\DeskPRO\Search\SearcherResult\Result;

class SearchController extends AbstractController
{
    public function searchAction()
    {
		$q = $this->in->getString('q');
		$search = App::getSearchAdapter();

		$results = array();
		$results_raw['ticket']    = $search->getTicketSearcher()->query($q, 25, 1, true);
		$results_raw['chat_conversation'] = $search->getChatConversationSearcher()->query($q, 25, 1, true);
		$results_raw['article']   = $search->getContentSearcher()->query($q, 25, 1, array('article'), true);
		$results_raw['download']  = $search->getContentSearcher()->query($q, 25, 1, array('download'), true);
		$results_raw['feedback']  = $search->getContentSearcher()->query($q, 25, 1, array('feedback'), true);
		$results_raw['news']      = $search->getContentSearcher()->query($q, 25, 1, array('news'), true);

		$results = array();
		foreach ($results_raw as $type => $set) {
			if ($set->count()) {
				$results[$type] = $search->getResultSetObjects($set, true);
			}
		}

		return $this->render('AgentBundle:Search:search.html.twig', array(
			'results' => $results
		));
	}

	public function searchResultsAction()
    {
		$q = $this->in->getString('q');
		$search = App::getSearchAdapter();

		$results_raw['ticket']    = $search->getTicketSearcher()->query($q, 25, 1, true);
		$results_raw['chat_conversation'] = $search->getChatConversationSearcher()->query($q, 25, 1, true);
		$results_raw['article']   = $search->getContentSearcher()->query($q, 25, 1, array('article'), true);
		$results_raw['download']  = $search->getContentSearcher()->query($q, 25, 1, array('download'), true);
		$results_raw['feedback']  = $search->getContentSearcher()->query($q, 25, 1, array('feedback'), true);
		$results_raw['news']      = $search->getContentSearcher()->query($q, 25, 1, array('news'), true);

		$results = array();
		foreach ($results_raw as $type => $set) {
			if ($set->count()) {
				$results[$type] = $search->getResultSetObjects($set, false);
			}
		}

		return $this->render('AgentBundle:Search:search.json.jsonphp', array(
			'router' => App::getRouter(),
			'results' => $results,
		));
	}
}

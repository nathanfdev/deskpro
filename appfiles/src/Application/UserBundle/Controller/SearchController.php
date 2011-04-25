<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\UserBundle\Controller;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Application\DeskPRO\Elastica\Searcher\ContentSearcher;

use \Orb\Util\Arrays;

class SearchController extends AbstractController
{
	public function searchAction()
	{
		$q = $this->in->getString('q');

		$is_search = false;
		$results = false;
		
		if ($q) {
			$searcher = new ContentSearcher(App::get('deskpro.elastica.manager'), $this->person);

			$is_search = true;
			$results = $searcher->search($q);
		}

		return $this->render('UserBundle:Search:search.html.twig', array(
			'is_search' => $is_search,
			'results'   => $results,
			'query' => $q
		));
	}

	public function labelledAction($labels)
	{
		$is_search = false;
		$results = false;

		if ($labels) {
			$searcher = new ContentSearcher(App::get('deskpro.elastica.manager'), $this->person);

			$is_search = true;
			$results = $searcher->labelled($labels);
		}

		return $this->render('UserBundle:Search:labelled.html.twig', array(
			'is_search' => $is_search,
			'results'   => $results,
			'labels' => $labels
		));
	}
}
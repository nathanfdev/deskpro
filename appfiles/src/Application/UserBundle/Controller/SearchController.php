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

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Application\DeskPRO\Elastica\Searcher\ContentSearcher;
use Application\DeskPRO\Labels\ContentLabelCloud;

use Application\DeskPRO\Search\StickyWordSearch;

use Orb\Util\Arrays;
use Orb\Util\Numbers;

class SearchController extends AbstractController
{
	public function searchAction()
	{
		$q = $this->in->getString('query');

		$is_search = false;
		$results = false;
		$sticky_results = false;

		if ($q) {
			$search = App::getSearchAdapter();
			$result_set = $search->getContentSearcher()->query($q);
			$results = $search->getResultSetObjects($result_set, true);

			$sticky_search = new StickyWordSearch($this->em);
			$sticky_results = $sticky_search->getResults($q, 5);

			$is_search = true;
		}

		return $this->render('UserBundle:Search:search.html.twig', array(
			'is_search'         => $is_search,
			'results'           => $results,
			'sticky_results'    => $sticky_results,
			'query'             => $q
		));
	}

	public function labelSearchAction($label = '', $type = 'all')
	{
		if (!$label) {
			$label = $this->in->getString('label');
			if ($label) {
				// Redirect label in query string (ie from form) to proper URL
				return $this->redirectRoute('user_search_labels', array('label' => $label));
			}
		}

		if (!$type OR !in_array($type, array('all', 'articles', 'ideas', 'downloads', 'news'))) {
			$type = 'all';
		}

		$page = $this->in->getUint('page');
		if (!$page) $page = 1;
		$page = max(1, $page);

		#------------------------------
		# Find content with label
		#------------------------------

		if ($label) {
			$type_searchers = array(
				'articles' => new \Application\DeskPRO\Searcher\ArticleSearch(),
				'ideas' => new \Application\DeskPRO\Searcher\IdeaSearch(),
				'downloads' => new \Application\DeskPRO\Searcher\DownloadSearch(),
				'news' => new \Application\DeskPRO\Searcher\NewsSearch(),
			);

			$results = array(
				'articles' => array(),
				'ideas' => array(),
				'downloads' => array(),
				'news' => array()
			);
			$is_single_type = false;

			if ($type == 'all') {
				foreach ($type_searchers as $typename => $searcher) {
					$searcher->addTerm('label', 'is', $label);
					$res = $searcher->getMatchingObjects(array('offset' => 0, 'max' => 5));
					$results[$typename] = array('results' => $res, 'show_more' => (count($res) >= 5));
				}

			} else {

				$per_page = 20;

				if ($page == 2 AND $this->request->isPartialRequest() == 'more') {
					// We're "moreing" after the original 5 results
					$offset = 5;
				} else {
					$offset = ($page - 1) * $per_page;
					if ($this->request->isPartialRequest() == 'more') {
						$offset += 5;
					}
				}

				$searcher = $type_searchers[$type];
				$searcher->addTerm('label', 'is', $label);

				$res = $searcher->getMatchingObjects(array('offset' => $offset, 'max' => $per_page));
				$results[$type] = array('results' => $res, 'show_more' => (count($res) >= $per_page));

				$is_single_type = true;

				if ($this->request->isPartialRequest() == 'more') {
					return $this->render('UserBundle:Search:label-search-items.html.twig', array(
						'typename' => $type,
						'results' => $results[$type]['results'],
						'show_more' => $results[$type]['show_more'],
					));
				}
			}
		}



		#------------------------------
		# Make combined search cloud
		#------------------------------

		$content_cloud = new ContentLabelCloud();
		$cloud = $content_cloud->getCloud();

		return $this->render('UserBundle:Search:label-search.html.twig', array(
			'cloud' => $cloud,
			'label' => $label,
			'results' => $results,
			'type' => $type,
			'is_single_type' => $is_single_type,
			'page' => $page,
		));
	}

	public function omnisearchAction($query)
	{
		$search = App::getSearchAdapter();
		$result_set = $search->getContentSearcher()->omnisearch($query);
		$results = $search->getResultSetObjects($result_set, true);

		return $this->render('UserBundle:Search:omnisearch.html.twig', array(
			'results' => $results,
			'query'   => $query,
		));
	}

	public function similarToAction($content_type)
	{
		$content = $this->request->query->get('content', '');

		$search = App::getSearchAdapter();
		$result_set = $search->getContentSearcher()->similarContent($content, array($content_type));
		$results = $search->getResultSetObjects($result_set, true);

		return $this->render('UserBundle:Search:similar-to.html.twig', array(
			'results' => $results,
		));
	}
}

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
 * @subpackage UserBundle
 */

namespace Application\UserBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\SearchLog;

use Application\DeskPRO\Elastica\Searcher\ContentSearcher;
use Application\DeskPRO\Labels\ContentLabelCloud;

use Application\DeskPRO\Search\StickyWordSearch;

use Orb\Util\Arrays;
use Orb\Util\Numbers;

class SearchController extends AbstractController
{
	public function searchAction()
	{
		$q = $this->in->getString('q');

		if ($this->in->getString('gourl')) {
			$gourl = $this->in->getString('gourl');
			$count = $this->in->getUint('c');

			$validate = $this->checkRequestToken($gourl . $count, 't');
			if ($validate || 1) {
				$searchlog = SearchLog::create($q, $count, true);
				$this->em->persist($searchlog);
				$this->em->flush();

				$this->session->set('from_search', true);
				$this->session->set('last_searchlog_id', $searchlog->id);
				$this->session->save();
			}
			return $this->redirect($this->in->getString('gourl'));
		}

		$is_search = false;
		$results = false;
		$sticky_results = false;

		$total = 0;
		$per_page = 25;
		$cur_page = 1;
		if ($this->in->getUint('p')) {
			$cur_page = $this->in->getUint('p');
		}

		if ($q) {
			$is_search  = true;
			$search     = App::getSearchAdapter();
			$search->setPersonContext($this->person);

			$result_set = $search->getContentSearcher()->query($q, $per_page, $cur_page);
			$total      = $result_set->totalCount();
			$results    = $search->getResultSetObjects($result_set, true);

			$sticky_search  = new StickyWordSearch($this->em);
			$sticky_results = $sticky_search->getResults($q, 5);

			$searchlog = SearchLog::create($q, count($results) + count($sticky_results), true);
			$this->em->transactional(function($em) use ($searchlog) {
				$em->persist($searchlog);
				$em->flush();
			});

			$this->session->set('last_searchlog_id', $searchlog->id);
			$this->session->save();
		}

		$pageinfo = Numbers::getPaginationPages($total, $cur_page, $per_page);

		return $this->render('UserBundle:Search:search.html.twig', array(
			'is_search'         => $is_search,
			'results'           => $results,
			'sticky_results'    => $sticky_results,
			'query'             => $q,
			'pageinfo'          => $pageinfo
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

		if (!$type OR !in_array($type, array('all', 'articles', 'feedback', 'downloads', 'news'))) {
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
				'feedback' => new \Application\DeskPRO\Searcher\FeedbackSearch(),
				'downloads' => new \Application\DeskPRO\Searcher\DownloadSearch(),
				'news' => new \Application\DeskPRO\Searcher\NewsSearch(),
			);

			$results = array(
				'articles' => array(),
				'feedback' => array(),
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
		 $content = isset($_REQUEST['content']) ? (string)$_REQUEST['content'] : '';

		$search = App::getSearchAdapter();
		$result_set = $search->getContentSearcher()->omnisearch($content, array($content_type));
		$results = $search->getResultSetObjects($result_set, true);

		return $this->render('UserBundle:Search:similar-to.html.twig', array(
			'results' => $results,
		));
	}
}

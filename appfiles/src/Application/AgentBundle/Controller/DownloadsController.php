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
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Searcher\DownloadSearch;
use Application\DeskPRO\UI\RuleBuilder;

use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Util;
use Orb\Util\Numbers;

use FineDiff;

class DownloadsController extends AbstractController
{
	############################################################################
	# view
	############################################################################

	public function viewAction($news_id)
	{
		$download = App::findEntity('DeskPRO:Download', $news_id);
		$download_cats = App::getEntityRepository('DeskPRO:DownloadCategory')->getCategoryHelper()->getFlatHierarchy();

		return $this->render('AgentBundle:News:view.html.twig', array(
			'download'           => $download,
			'download_cats'      => $download_cats,
		));
	}

	############################################################################
	# list
	############################################################################

	/**
	 * View a list of ideas
	 */
	public function listAction($category_id = 0)
	{
		$category = null;
		if ($category_id) {
			$category = App::findEntity('DeskPRO:DownloadCategory', $category_id);
		}

		$searcher = new DownloadSearch();
		$searcher->setPersonContext($this->person);

		if ($category) {
			$searcher->addTerm(DownloadSearch::TERM_CATEGORY, 'is', $category['id']);
		}

		$terms = null;
		if ($category) {
			$searcher->addTerm(DownloadSearch::TERM_CATEGORY, 'is', $category['id']);
		} else {
			if ($this->in->getCleanValueArray('terms', 'raw' , 'discard')) {
				$term_rules = RuleBuilder::newTermsBuilder();
				$terms = $term_rules->readForm($this->in->getCleanValueArray('terms', 'raw' , 'discard'));

				foreach ($terms as $term) {
					$searcher->addTerm($term['type'], $term['op'], $term['options']);
				}
			}
		}

		$total = $searcher->getCount();
		$per_page = 20;
		$pageinfo = Numbers::getPaginationPages($total, $this->in->getUint('page'), $per_page);

		$limit = array(
			'offset' => ($pageinfo['curpage'] - 1) * $per_page,
			'max' => $per_page
		);

		$result_ids = $searcher->getMatches($limit);

		$results = App::getEntityRepository('DeskPRO:Download')->getByResultIds($result_ids);

		$tpl = 'AgentBundle:Downloads:list.html.twig';
		if ($this->request->isPartialRequest()) {
			$tpl = 'AgentBundle:Downloads:list-page.html.twig';
		}

		$download_options = array();
		$download_options['categories'] = App::getEntityRepository('DeskPRO:DownloadCategory')->getCategoryHelper()->getFlatHierarchy();

		return $this->render($tpl, array(
			'results'            => $results,
			'download_options'   => $download_options,
			'search_form'        => array('terms' => $searcher->getTerms()),
			'terms_summary'      => $searcher->getSummary(),
			'category'           => $category,
			'pageinfo'           => $pageinfo,
			'page'               => $pageinfo['curpage']
		));
	}
}
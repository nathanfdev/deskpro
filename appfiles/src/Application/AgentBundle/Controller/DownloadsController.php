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
use Application\DeskPRO\Entity\DownloadComment;
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

	public function viewAction($download_id)
	{
		$download = App::findEntity('DeskPRO:Download', $download_id);
		$download_cats = App::getEntityRepository('DeskPRO:DownloadCategory')->getCategoryHelper()->getFlatHierarchy();
		$download_comments = App::getEntityRepository('DeskPRO:DownloadComment')->getComments($download);

		return $this->render('AgentBundle:Downloads:view.html.twig', array(
			'download'           => $download,
			'download_comments'  => $download_comments,
			'download_cats'      => $download_cats,
		));
	}

	public function ajaxSaveLabelsAction($download_id)
	{
		$download = App::findEntity('DeskPRO:Download', $download_id);

		$labels = $this->in->getCleanValueArray('labels', 'string', 'discard');

		$download->getLabelManager()->setLabelsArray($labels);

		App::getOrm()->persist($download);
		App::getOrm()->flush();

		return $this->createJsonResponse(array('success' => 1));
	}

	public function ajaxSaveCommentAction($download_id)
	{
		$download = App::findEntity('DeskPRO:Download', $download_id);

		$comment = new DownloadComment();
		$comment->download = $download;
		$comment->person = $this->person;
		$comment['content'] = $this->in->getString('content');
		$comment['status'] = 'visible';
		$comment['date_created']  = new \DateTime();

		App::getOrm()->persist($comment);
		App::getOrm()->flush();

		return $this->render('AgentBundle:Downloads:view-comment.html.twig', array(
			'comment' => $comment
		));
	}

	public function ajaxSaveAction($download_id)
	{
		$download = App::findEntity('DeskPRO:Download', $download_id);

		$action = $this->in->getString('action');

		$data = array('success' => 1);

		switch ($action) {
			case 'title':
				$download['title'] = $this->in->getString('title');
				break;

			case 'content':
				$download['content'] = $this->in->getString('content');
				$data['content_html'] = $download->getContentHtml();
				break;
		}

		App::getOrm()->persist($download);
		App::getOrm()->flush();

		return $this->createJsonResponse($data);
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
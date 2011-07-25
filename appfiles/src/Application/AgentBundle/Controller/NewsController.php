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
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsComment;
use Application\DeskPRO\Searcher\NewsSearch;
use Application\DeskPRO\UI\RuleBuilder;

use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Util;
use Orb\Util\Numbers;

use FineDiff;

/**
 * Handles listing and editing of news
 */
class NewsController extends AbstractController
{
	############################################################################
	# view
	############################################################################

	public function viewAction($news_id)
	{
		$news = App::findEntity('DeskPRO:News', $news_id);
		$news_comments = App::getEntityRepository('DeskPRO:NewsComment')->getComments($news);
		$news_cats = App::getEntityRepository('DeskPRO:NewsCategory')->getCategoryHelper()->getFlatHierarchy();

		return $this->render('AgentBundle:News:view.html.twig', array(
			'news'           => $news,
			'news_comments'  => $news_comments,
			'news_cats'      => $news_cats,
		));
	}

	public function ajaxSaveLabelsAction($news_id)
	{
		$news = App::findEntity('DeskPRO:News', $news_id);

		$labels = $this->in->getCleanValueArray('labels', 'string', 'discard');

		$news->getLabelManager()->setLabelsArray($labels);

		App::getOrm()->persist($news);
		App::getOrm()->flush();

		return $this->createJsonResponse(array('success' => 1));
	}

	public function ajaxSaveCommentAction($news_id)
	{
		$news = App::findEntity('DeskPRO:News', $news_id);
		
		$comment = new NewsComment();
		$comment->news = $news;
		$comment->person = $this->person;
		$comment['content'] = $this->in->getString('content');
		$comment['status'] = 'visible';
		$comment['date_created']  = new \DateTime();

		App::getOrm()->persist($comment);
		App::getOrm()->flush();

		return $this->render('AgentBundle:News:view-comment.html.twig', array(
			'comment' => $comment
		));
	}

	public function ajaxSaveAction($news_id)
	{
		$news = App::findEntity('DeskPRO:News', $news_id);

		$action = $this->in->getString('action');

		$data = array('success' => 1);

		switch ($action) {
			case 'title':
				$news['title'] = $this->in->getString('title');
				break;

			case 'content':
				$news['content'] = $this->in->getString('content');
				$data['content_html'] = $news->getContentHtml();
				break;
		}

		App::getOrm()->persist($news);
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
			$category = App::findEntity('DeskPRO:NewsCategory', $category_id);
		}

		$searcher = new \Application\DeskPRO\Searcher\NewsSearch();
		$searcher->setPersonContext($this->person);

		$terms = null;
		if ($category) {
			$searcher->addTerm(NewsSearch::TERM_CATEGORY, 'is', $category['id']);
			$searcher->addTerm('is_published', 'is', 1);
		} else {
			if ($this->in->getCleanValueArray('terms', 'raw' , 'discard')) {
				$term_rules = RuleBuilder::newTermsBuilder();
				$terms = $term_rules->readForm($this->in->getCleanValueArray('terms', 'raw' , 'discard'));

				foreach ($terms as $term) {
					$searcher->addTerm($term['type'], $term['op'], $term['options']);
				}
			} else {
				$searcher->addTerm('is_published', 'is', 1);
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

		$results = App::getEntityRepository('DeskPRO:News')->getByResultIds($result_ids);

		$tpl = 'AgentBundle:News:list.html.twig';
		if ($this->request->isPartialRequest()) {
			$tpl = 'AgentBundle:News:list-page.html.twig';
		}

		$news_options = array();
		$news_options['categories'] = App::getEntityRepository('DeskPRO:NewsCategory')->getCategoryHelper()->getFlatHierarchy();

		return $this->render($tpl, array(
			'results'        => $results,
			'news_options'   => $news_options,
			'search_form'    => array('terms' => $searcher->getTerms()),
			'terms_summary'  => $searcher->getSummary(),
			'category'       => $category,
			'pageinfo'       => $pageinfo,
			'page'           => $pageinfo['curpage']
		));
	}
}
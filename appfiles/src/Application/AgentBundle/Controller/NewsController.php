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

use Application\AgentBundle\Controller\Helper\NewsResults;

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

		$show_all = false;
		if (!$category) {
			$show_all = $this->in->getBool('all');
		}

		$result_helper = NewsResults::newFromRequest($this, array(
			'category' => $category,
			'show_all' => $show_all
		));

		$page = $this->in->getUint('p');
		if (!$page) $page = 1;

		$results = $result_helper->getNewsForPage($page);
		$result_cache = $result_helper->getResultCache();

		$display_fields = $this->person->getPref('agent.ui.news-filter-display-fields.' . $result_cache['id']);
		if (!$display_fields) {
			$display_fields = array('author', 'date_created');
		}

		$tpl = 'AgentBundle:News:filter.html.twig';
		if ($this->request->isPartialRequest()) {
			$tpl = 'AgentBundle:News:filter-page.html.twig';
		}

		return $this->render($tpl, array(
			'results'        => $results,
			'result_id'      => $result_cache['id'],
			'display_fields'  => $display_fields,
			'category'       => $category,
		));
	}
}
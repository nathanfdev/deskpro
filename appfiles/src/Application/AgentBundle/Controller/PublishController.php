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
use Application\DeskPRO\Entity\GlossaryWord;
use Application\DeskPRO\EntityRepository\CommentAbstract as CommentAbstractRepos;

use Application\DeskPRO\Publish\AgentHelper as PublishHelper;
use Application\DeskPRO\Publish\CategoryEdit as PublishCategoryEdit;

use Orb\Util\Strings;
use Orb\Util\Numbers;
use Orb\Util\Arrays;
use Orb\Util\Util;

class PublishController extends AbstractController
{
	public function getSectionDataAction()
	{
		$data = array();

		#------------------------------
		# KB
		#------------------------------

		$kb_counts = array();
		$kb_counts['awaiting_validation_articles'] = App::getDb()->fetchColumn("SELECT COUNT(*) FROM articles WHERE hidden_status = ?", array('validating'));
		$kb_counts['awaiting_validation_edits']    = App::getDb()->fetchColumn("SELECT COUNT(*) FROM article_validating_edits");
		$kb_counts['awaiting_validation']          = $kb_counts['awaiting_validation_articles'] + $kb_counts['awaiting_validation_edits'];
		$kb_counts['drafts']                       = App::getDb()->fetchColumn("SELECT COUNT(*) FROM articles WHERE hidden_status = ?", array('draft'));
		$kb_counts['pending']                      = App::getDb()->fetchColumn("SELECT COUNT(*) FROM article_pending_create");

		$publish_helper = new PublishHelper();
		$publish_helper->setPersonContext($this->person);

		$kb_cats              = $publish_helper->getCategoryStructure(PublishHelper::ARTICLES);
		$kb_cats_counts       = $publish_helper->getCategoryCounts(PublishHelper::ARTICLES);

		$news_cats            = $publish_helper->getCategoryStructure(PublishHelper::NEWS);
		$news_cats_counts     = $publish_helper->getCategoryCounts(PublishHelper::NEWS);

		$download_cats        = $publish_helper->getCategoryStructure(PublishHelper::DOWNLOADS);
		$download_cats_counts = $publish_helper->getCategoryCounts(PublishHelper::DOWNLOADS);

		$glossary_words     = $publish_helper->getGlossaryWordsIndex();

		$counts = array();
		$counts['validating_comments'] = CommentAbstractRepos::getCombinedValidatingCount();


		$data['section_html'] = $this->renderView('AgentBundle:Publish:window-section.html.twig', array(
			'counts'                => $counts,

			'kb_counts'             => $kb_counts,
			'kb_cats'               => $kb_cats,
			'kb_cats_counts'        => $kb_cats_counts,

			'news_cats'             => $news_cats,
			'news_cats_counts'      => $news_cats_counts,

			'download_cats'         => $download_cats,
			'download_cats_counts'  => $download_cats_counts,

			'glossary_words'        => $glossary_words,
		));

		return $this->createJsonResponse($data);
	}

	############################################################################
	# comments
	############################################################################

	public function listValidatingCommentsAction()
	{
		$per_page = 25;

		$curpage = $this->in->getUint('page');
		if (!$curpage) $curpage = 1;

		$limit = array(
			'max' => $per_page,
			'offset' => ($curpage - 1) * $per_page
		);

		$pageinfo = null;
		$total = null;
		if (!$this->request->isPartialRequest()) {
			$total = CommentAbstractRepos::getCombinedValidatingCount();
			$pageinfo = Numbers::getPaginationPages($total, $curpage, $per_page);
		}

		$comments = CommentAbstractRepos::getCombinedValidating($limit);

		$tpl = 'AgentBundle:Publish:validating-comments.html.twig';
		if ($this->request->isPartialRequest()) {
			$tpl = 'AgentBundle:Publish:validating-comments-page.html.twig';
		}

		return $this->render($tpl, array(
			'comments' => $comments,
			'total'    => $total,
			'pageinfo' => $pageinfo
		));
	}

	public function approveCommentAction($typename, $comment_id)
	{
		$entity = $this->_getCommentEntityName($typename);

		$comment = App::findEntity($entity, $comment_id);
		$comment['status'] = 'visible';

		App::getOrm()->persist($comment);
		App::getOrm()->flush();

		return $this->createJsonResponse(array(
			'comment_id' => $comment['id'],
			'typename'   => $typename
		));
	}

	public function disapproveCommentAction($typename, $comment_id)
	{
		$entity = $this->_getCommentEntityName($typename);

		$comment = App::findEntity($entity, $comment_id);
		$comment['status'] = 'deleted';

		App::getOrm()->persist($comment);
		App::getOrm()->flush();

		return $this->createJsonResponse(array(
			'comment_id' => $comment['id'],
			'typename'   => $typename
		));
	}

	protected function _getCommentEntityName($classname)
	{
		if ($classname instanceof \Application\DeskPRO\Entity\ArticleComment) {
			return 'DeskPRO:ArticleComment';
		} elseif ($classname instanceof \Application\DeskPRO\Entity\DownloadComment) {
			return 'DeskPRO:DownloadComment';
		} elseif ($classname instanceof \Application\DeskPRO\Entity\IdeaComment) {
			return 'DeskPRO:IdeaComment';
		} elseif ($classname instanceof \Application\DeskPRO\Entity\NewsComment) {
			return 'DeskPRO:NewsComment';
		}

		return $classname;
	}

	############################################################################
	# saving categories
	############################################################################

	public function saveCategoriesAction($type)
	{
		$entity_name = null;
		switch ($type) {
			case 'article':   $entity_name = 'DeskPRO:ArticleCategory';   break;
			case 'download':  $entity_name = 'DeskPRO:DownloadCategory';  break;
			case 'news':      $entity_name = 'DeskPRO:NewsCategory';      break;
		}

		if (!$entity_name) {
			return $this->createJsonResponse(array('Invalid type'));
		}

		$class = App::getEntityClass($entity_name);

		$categories = $this->in->getCleanValueArray('cats');

		$new_cats = array();

		$this->em->beginTransaction();

		foreach ($categories as $cat_info) {
			if ($cat_info['isNew']) {
				$cat = new $class();
				if ($cat_info['parentId']) {
					if (isset($new_cats[$cat_info['parentId']])) {
						$cat['parent'] = $new_cats[$cat_info['parentId']];
					} else {
						$cat['parent'] = App::getEntityRepository($entity_name)->find($cat_info['parentId']);
					}
				}
			} else {
				$cat = App::getEntityRepository($entity_name)->find($cat_info['id']);
			}

			$cat['title'] = $cat_info['title'];
			$cat['display_order'] = $cat_info['displayOrder'];

			$this->em->persist($cat);
		}

		$this->em->transactional(function($em) {
			$em->flush();
			$em->commit();
		});

		return $this->createJsonResponse(array('success' => true));
	}

	public function updateCategoryTitlesAction($type)
	{
		PublishCategoryEdit::updateTitles($type, $this->in->getCleanValueArray('titles', 'string', 'uint'));
		return $this->createJsonResponse(array(
			'success' => true
		));
	}

	public function updateCategoryOrdersAction($type)
	{
		PublishCategoryEdit::updateOrders($type, $this->in->getCleanValueArray('orders', 'uint', 'discard'));
		return $this->createJsonResponse(array(
			'success' => true
		));
	}

	public function updateCategoryStructureAction($type)
	{
		PublishCategoryEdit::updateStructure($type, $this->in->getCleanValueArray('structure', 'uint', 'uint'));
		return $this->createJsonResponse(array(
			'success' => true
		));
	}

	public function addCategoryAction($type)
	{
		$cat = PublishCategoryEdit::addCategory($type, $this->in->getString('title'));
		return $this->createJsonResponse(array(
			'success' => true,
			'id' => $cat['id'],
			'url' => ''
		));
	}
}

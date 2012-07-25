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
 * @subpackage AgentBundle
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
	/**
	 * @var \Application\DeskPRO\Publish\AgentHelper
	 */
	protected $publish_helper;

	protected function init()
	{
		parent::init();

		$this->publish_helper = new PublishHelper();
		$this->publish_helper->setPersonContext($this->person);

		if ($this->in->getString('specific_type')) {
			$this->publish_helper->setEnabledTypes(array($this->in->getString('specific_type')));
		}
	}

	public function getSectionDataAction()
	{
		$data = array();

		#------------------------------
		# KB
		#------------------------------

		$kb_cats              = $this->publish_helper->getCategoryStructure(PublishHelper::ARTICLES);
		$kb_repo              = $this->em->getRepository('DeskPRO:ArticleCategory');
		$kb_cats_counts       = $this->publish_helper->getCategoryCounts(PublishHelper::ARTICLES);
		$kb_cats_usergroups   = $this->publish_helper->getCategoryUsergroups(PublishHelper::ARTICLES);

		$news_cats            = $this->publish_helper->getCategoryStructure(PublishHelper::NEWS);
		$news_repo            = $this->em->getRepository('DeskPRO:NewsCategory');
		$news_cats_counts     = $this->publish_helper->getCategoryCounts(PublishHelper::NEWS);
		$news_cats_usergroups = $this->publish_helper->getCategoryUsergroups(PublishHelper::NEWS);

		$download_cats        = $this->publish_helper->getCategoryStructure(PublishHelper::DOWNLOADS);
		$download_repo        = $this->em->getRepository('DeskPRO:DownloadCategory');
		$download_cats_counts = $this->publish_helper->getCategoryCounts(PublishHelper::DOWNLOADS);
		$download_cats_usergroups = $this->publish_helper->getCategoryUsergroups(PublishHelper::DOWNLOADS);

		$glossary_words     = $this->publish_helper->getGlossaryWordsIndex();
		$glossary_count     = Arrays::countMulti($glossary_words);

		$counts = array();
		$counts['validating_comments']   = $this->publish_helper->getValidatingCommentsCount();
		$counts['validating_content']    = $this->publish_helper->getValidatingContentCount();
		$counts['drafts']                = $this->publish_helper->getDraftsCount();
		$counts['all_drafts']            = $this->publish_helper->getDraftsCount(false);
		$counts['pending']               = $this->db->fetchColumn("SELECT COUNT(*) FROM article_pending_create");

		$usergroups = $this->container->getDataService('Usergroup')->getUserUsergroups();

		$data['section_html'] = $this->renderView('AgentBundle:Publish:window-section.html.twig', array(
			'usergroups'            => $usergroups,
			'counts'                => $counts,

			'kb_cats'               => $kb_cats,
			'kb_repo'               => $kb_repo,
			'kb_cats_counts'        => $kb_cats_counts,
			'kb_cats_usergroups'    => $kb_cats_usergroups,

			'news_cats'             => $news_cats,
			'news_repo'             => $news_repo,
			'news_cats_counts'      => $news_cats_counts,
			'news_cats_usergroups'  => $news_cats_usergroups,

			'download_cats'         => $download_cats,
			'download_repo'         => $download_repo,
			'download_cats_counts'  => $download_cats_counts,
			'download_cats_usergroups' => $download_cats_usergroups,

			'glossary_words'        => $glossary_words,
			'glossary_count'        => $glossary_count,
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
			$total = $this->publish_helper->getValidatingCommentsCount();
			$pageinfo = Numbers::getPaginationPages($total, $curpage, $per_page);
		}

		$validating_comments = $this->publish_helper->getValidatingComments($limit);

		$tpl = 'AgentBundle:Publish:validating-comments.html.twig';
		if ($this->request->isPartialRequest()) {
			$tpl = 'AgentBundle:Publish:validating-comments-page.html.twig';
		}

		return $this->render($tpl, array(
			'single_type' => $this->publish_helper->getSingleSpecificType(),
			'validating_comments' => $validating_comments,
			'total'    => $total,
			'pageinfo' => $pageinfo
		));
	}

	public function approveCommentAction($typename, $comment_id)
	{
		$entity = $this->_getCommentEntityName($typename);

		$comment = $this->em->find($entity, $comment_id);
		$comment['status'] = 'visible';

		$this->em->persist($comment);
		$this->em->flush();

		$this->_sendCommentApprovedNotification($comment);

		return $this->createJsonResponse(array(
			'comment_id' => $comment['id'],
			'typename'   => $typename
		));
	}

	public function deleteCommentAction($typename, $comment_id)
	{
		$entity = $this->_getCommentEntityName($typename);

		$comment = $this->em->find($entity, $comment_id);
		$this->em->remove($comment);
		$this->em->flush();

		$this->_sendCommentDeletedNotification($comment);

		return $this->createJsonResponse(array(
			'comment_id' => $comment['id'],
			'typename'   => $typename
		));
	}

	public function validatingCommentsMassActionsAction($action)
	{
		$data = $this->in->getCleanValueArray('content', 'array', 'string');

		$this->em->beginTransaction();

		foreach ($data as $typename => $ids) {
			$entity = $this->_getCommentEntityName($typename);
			if (!$entity) continue;

			$results = $this->em->getRepository($entity)->getByIds($ids);
			foreach ($results as $r) {
				if ($action == 'approve') {
					$r->status = 'visible';
					$this->_sendCommentApprovedNotification($r);
				} else {
					$r->status = 'deleted';
					$this->_sendCommentDeletedNotification($r);
				}

				$this->em->persist($r);
			}
		}

		$this->em->flush();
		$this->em->commit();

		return $this->createJsonResponse(array(
			'success' => true
		));
	}

	public function commentInfoAction($typename, $comment_id)
	{
		$entity = $this->_getCommentEntityName($typename);

		$comment = $this->em->find($entity, $comment_id);

		return $this->createJsonResponse(array(
			'comment_id' => $comment['id'],
			'content_type'   => $typename,
			'comment_text' => $comment->content
		));
	}

	public function saveCommentAction($typename, $comment_id)
	{
		$entity = $this->_getCommentEntityName($typename);

		$comment = $this->em->find($entity, $comment_id);
		$comment->content = $this->in->getString('comment');

		$this->em->persist($comment);
		$this->em->flush();

		return $this->createJsonResponse(array(
			'comment_id' => $comment['id'],
			'content_type'   => $typename,
			'comment_html' => $comment->getContentHtml()
		));
	}

	public function getNewTicketCommentInfoAction($typename, $comment_id)
	{
		$entity = $this->_getCommentEntityName($typename);

		$comment = $this->em->find($entity, $comment_id);

		return $this->createJsonResponse(array(
			'message'       => $comment->getContentPlain(),
			'status'        => $comment->status,
			'content_type'  => $typename,
			'comment_id'    => $comment_id,
			'name'          => $comment->getPerson()->display_name,
			'person_id'     => $comment->getPersonId(),
			'email'         => $comment->getUserEmail(),
			'object_title'  => $comment->getObject()->getTitle(),
			'object_url'    => $this->get('router')->getGenerator()->generateObjectUrl($comment->getObject())
		));
	}

	protected function _getCommentEntityName($typename)
	{
		switch ($typename) {
			case 'articles':
				return 'DeskPRO:ArticleComment';
			case 'downloads':
				return 'DeskPRO:DownloadComment';
			case 'news':
				return 'DeskPRO:NewsComment';
			case 'feedback':
				return 'DeskPRO:FeedbackComment';
		}
	}

	protected function _sendCommentApprovedNotification($comment)
	{
		if ($comment->getUserEmail()) {
			$message = $this->container->getMailer()->createMessage();
			if ($comment->person) {
				$message->setTo($comment->person->getPrimaryEmailAddress(), $comment->person->getDisplayName());
			} else {
				$message->setTo($comment->getUserEmail());
			}
			$message->setTemplate('DeskPRO:emails_user:comment-approved.html.twig', array(
				'comment' => $comment
			));
			$message->enableQueueHint();
			$this->container->getMailer()->send($message);
		}

		// For feedback we also notify everyone involved
		if ($comment instanceof \Application\DeskPRO\Entity\FeedbackComment) {
			$commenting = new \Application\DeskPRO\Feedback\FeedbackCommenting($this->container, $this->person);
			$commenting->newCommentNotify($comment);
		}
	}

	public function _sendCommentDeletedNotification($comment)
	{
		if ($comment->getUserEmail()) {
			$message = $this->container->getMailer()->createMessage();
			if ($comment->person) {
				$message->setTo($comment->person->getPrimaryEmailAddress(), $comment->person->getDisplayName());
			} else {
				$message->setTo($comment->getUserEmail());
			}
			$message->setTemplate('DeskPRO:emails_user:comment-deleted.html.twig', array(
				'comment' => $comment
			));
			$message->enableQueueHint();
			$this->container->getMailer()->send($message);
		}
	}

	############################################################################
	# content validating
	############################################################################

	public function listValidatingContentAction()
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
			$total = $this->publish_helper->getValidatingContentCount();
			$pageinfo = Numbers::getPaginationPages($total, $curpage, $per_page);
		}

		$content_validating =  $this->publish_helper->getValidatingContent($limit);

		$tpl = 'AgentBundle:Publish:validating-content.html.twig';
		if ($this->request->isPartialRequest()) {
			$tpl = 'AgentBundle:Publish:validating-content-page.html.twig';
		}

		return $this->render($tpl, array(
			'single_type' => $this->publish_helper->getSingleSpecificType(),
			'content_validating' => $content_validating,
			'total'    => $total,
			'pageinfo' => $pageinfo
		));
	}

	public function listValidatingFeedbackCommentsAction()
	{
		$this->publish_helper->setEnabledTypes(array('feedback'));
		return $this->listValidatingCommentsAction();
	}

	public function listValidatingFeedbackContentAction()
	{
		$this->publish_helper->setEnabledTypes(array('feedback'));
		return $this->listValidatingContentAction();
	}

	public function approveContentAction($type, $content_id)
	{
		$content_validating =  $this->publish_helper->getValidatingContentInfo(1000);

		$entity =  $this->publish_helper->getEntityNameFor($type);
		$obj = $this->em->getRepository($entity)->find($content_id);

		if ($obj) {
			if ($obj instanceof \Application\DeskPRO\Entity\Feedback) {
				$this->approveFeedback($obj);
			} else {
				$obj->status = 'published';
				$this->em->beginTransaction();
				$this->em->persist($obj);
				$this->em->flush();
				$this->em->commit();
			}
		}

		$next = $this->_findNextValidating($content_validating, $type, $content_id);

		$next_url = null;
		if ($next) {
			$next_url = $this->get('router')->getGenerator()->generateObjectUrl($next, array(), 'agent');
		}

		return $this->createJsonResponse(array(
			'success' => true,
			'next_url' => $next_url
		));
	}

	public function approveFeedback(\Application\DeskPRO\Entity\Feedback $feedback)
	{
		$feedback_moderate = new \Application\DeskPRO\Feedback\FeedbackModerate($this->container, $this->person);
		$feedback_moderate->approveFeedback($feedback);
	}

	public function disapproveFeedback(\Application\DeskPRO\Entity\Feedback $feedback, $reason)
	{
		$feedback_moderate = new \Application\DeskPRO\Feedback\FeedbackModerate($this->container, $this->person);
		$feedback_moderate->disapproveFeedback($feedback, $reason);
	}

	public function disapproveContentAction($type, $content_id)
	{
		$content_validating =  $this->publish_helper->getValidatingContentInfo(1000);

		$entity = $this->publish_helper->getEntityNameFor($type);
		$obj = $this->em->getRepository($entity)->find($content_id);

		if ($obj) {
			if ($obj instanceof \Application\DeskPRO\Entity\Feedback) {
				$this->disapproveFeedback($obj, $this->in->getString('reason'));
			} else {
				$obj->status_code = 'hidden.draft';
				$reason = $this->in->getString('reason');
				if (0 && $reason) {
					$agent_chat = new \Application\DeskPRO\Chat\AgentChat($this->person, $this->session->getEntity());
					$reason .= ' (<a data-route="' . $this->get('router')->getGenerator()->generateObjectUrl($obj, array(), 'agent') .'">' . htmlentities($obj->title) . '</a>)';
					$agent_chat->sendAgentMessage($reason, array($obj->person['id']));
				}

				$this->em->beginTransaction();
				$this->em->persist($obj);
				$this->em->flush();
				$this->em->commit();
			}
		}

		$next = $this->_findNextValidating($content_validating, $type, $content_id);

		$next_url = null;
		if ($next) {
			$next_url = $this->get('router')->getGenerator()->generateObjectUrl($next, array(), 'agent');
		}

		return $this->createJsonResponse(array(
			'success' => true,
			'next_url' => $next_url
		));
	}

	public function nextValidatingContentAction($type, $content_id)
	{
		$content_validating =  $this->publish_helper->getValidatingContentInfo(1000);

		$next = $this->_findNextValidating($content_validating, $type, $content_id);

		$next_url = null;
		if ($next) {
			$next_url = $this->get('router')->getGenerator()->generateObjectUrl($next, array(), 'agent');
		}

		return $this->createJsonResponse(array(
			'success' => true,
			'next_url' => $next_url
		));
	}

	protected function _findNextValidating($content_validating, $type, $content_id)
	{
		$do_ret = false;

		foreach ($content_validating as $info) {
			if ($do_ret) {
				$entity =  $this->publish_helper->getEntityNameFor($info['content_type']);
				$obj = $this->em->getRepository($entity)->find($info['content_id']);
				if ($obj) {
					return $obj;
				}
			} else if ($info['content_type'] == $type && $info['content_id'] == $content_id) {
				$do_ret = true;
			}
		}

		if (!$content_validating) {
			return null;
		}

		// If we got here, just return the first
		$info = array_shift($content_validating);
		$entity =  $this->publish_helper->getEntityNameFor($info['content_type']);
		$obj = $this->em->getRepository($entity)->find($info['content_id']);
		if ($obj) {
			return $obj;
		}

		return null;
	}

	public function validatingMassActionsAction($action)
	{
		$data = $this->in->getCleanValueArray('content', 'array', 'string');

		$this->em->beginTransaction();

		$agent_chat = new \Application\DeskPRO\Chat\AgentChat($this->person, $this->session->getEntity());
		$reason = $this->in->getString('decline_reason');

		foreach ($data as $type => $ids) {
			$entity =  $this->publish_helper->getEntityNameFor($type);
			if (!$entity) continue;

			$results = $this->em->getRepository($entity)->getByIds($ids);
			foreach ($results as $r) {
				if ($action == 'approve') {
					$r->status = 'approve';
				} else {
					if ($reason) {
						$this_reason .= $reason . ' (<a data-route="' . $this->get('router')->getGenerator()->generateObjectUrl($obj, array(), 'agent') .'">' . htmlentities($obj->title) . '</a>)';
						$agent_chat->sendAgentMessage($this_reason, array($r->person['id']));
					}
					$r->status_code = 'hidden.draft';
				}

				$this->em->persist($r);
			}
		}

		$this->em->flush();
		$this->em->commit();

		return $this->createJsonResponse(array(
			'success' => true
		));
	}

	############################################################################
	# content validating
	############################################################################

	public function listDraftsAction($type)
	{
		if($type == 'all') {
			return $this->listDrafts(true);
		}
		else {
			return $this->listDrafts(false);
		}
	}

	protected function listDrafts($get_all)
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
			$total = $this->publish_helper->getDraftsCount();
			$pageinfo = Numbers::getPaginationPages($total, $curpage, $per_page);
		}

		$drafts =  $this->publish_helper->getDraftContent(null, 'ASC', $get_all);

		$tpl = 'AgentBundle:Publish:drafts.html.twig';
		if ($this->request->isPartialRequest()) {
			$tpl = 'AgentBundle:Publish:drafts-page.html.twig';
		}

		return $this->render($tpl, array(
			'drafts'   => $drafts,
			'total'    => $total,
			'pageinfo' => $pageinfo,
			'all'      => $get_all,
		));
	}

	public function draftsMassActionsAction($action)
	{
		$data = $this->in->getCleanValueArray('content', 'array', 'string');

		$this->em->beginTransaction();

		$affected_content = array();

		foreach ($data as $type => $ids) {
			$entity =  $this->publish_helper->getEntityNameFor($type);
			if (!$entity) continue;

			$results = $this->em->getRepository($entity)->getByIds($ids);
			foreach ($results as $r) {
				if ($r['status_code'] != 'hidden.draft' OR $r->person['id'] != $this->person['id']) continue;
				if ($action == 'delete') {
					$this->em->remove($r);
					$affected_content[] = array('typename' => $type, 'contentId' => $r->id);
				} elseif ($action == 'publish') {
					$r->setStatusCode('published');
					$affected_content[] = array('typename' => $type, 'contentId' => $r->id);
				}
			}
		}

		$this->em->flush();
		$this->em->commit();

		return $this->createJsonResponse(array(
			'success' => true,
			'affected' => $affected_content
		));
	}

	############################################################################
	# saving sticky words
	############################################################################

	public function saveStickySearchWordsAction($type, $content_id)
	{
		$entity_name = null;
		switch ($type) {
			case 'articles':   $entity_name = 'DeskPRO:Article';   break;
			case 'downloads':  $entity_name = 'DeskPRO:Download';  break;
			case 'news':       $entity_name = 'DeskPRO:News';      break;
			case 'feedback':      $entity_name = 'DeskPRO:Feedback';      break;
		}

		$this->db->beginTransaction();

		// Lets just recreate them all
		$this->db->executeUpdate("
			DELETE FROM search_sticky_result
			WHERE object_type = ? AND object_id = ?
		", array($entity_name, $content_id));

		foreach ($this->in->getCleanValueArray('words', 'string', 'discard') as $word) {
			$word = Strings::utf8_strtolower($word);
			$word = Strings::utf8_accents_to_ascii($word);

			$this->db->insert('search_sticky_result', array(
				'word'        => $word,
				'object_type' => $entity_name,
				'object_id'   => $content_id
			));
		}

		$this->db->commit();

		return $this->createJsonResponse(array(
			'success' => 1
		));
	}

	############################################################################
	# ratings
	############################################################################

	public function ratingWhoVotedAction($object_type, $object_id)
	{
		$ratings = $this->em->createQuery("
			SELECT r
			FROM DeskPRO:Rating r
			LEFT JOIN r.person p
			WHERE r.object_type = ?1 AND r.object_id = ?2
			ORDER BY r.id DESC
		")->execute(array(1=> $object_type, 2=> $object_id));

		return $this->render('AgentBundle:Publish:rating-who-voted.html.twig', array(
			'ratings' => $ratings,
			'object_type' => $object_type,
		));
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
						$cat['parent'] = $this->em->getRepository($entity_name)->find($cat_info['parentId']);
					}
				}
			} else {
				$cat = $this->em->getRepository($entity_name)->find($cat_info['id']);
			}

			$cat['title'] = $cat_info['title'];
			$cat['display_order'] = $cat_info['displayOrder'];

			$this->em->persist($cat);
		}

		$this->em->transactional(function($em) {
			$em->flush();
			$em->commit();
		});

		$this->container->getSystemService('publish_structure_cache')->flush();

		return $this->createJsonResponse(array('success' => true));
	}

	public function updateCategoryTitlesAction($type)
	{
		PublishCategoryEdit::updateTitles($type, $this->in->getCleanValueArray('titles', 'string', 'uint'));
		return $this->createJsonResponse(array(
			'success' => true
		));
	}

	public function updateCategoryAction($type, $category_id)
	{
		PublishCategoryEdit::update(
			$type,
			$category_id,
			$this->in->getString('title'),
			$this->in->getCleanValueArray('usergroup_ids', 'uint', 'discard')
		);
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
		try {
			PublishCategoryEdit::updateStructure(
				$type,
				$this->in->getCleanValueArray('structure', 'uint', 'uint'),
				$this->in->getCleanValueArray('structure_check', 'uint', 'uint')
			);
		} catch (\OutOfBoundsException $e) {
			return $this->createJsonResponse(array(
				'error' => true
			));
		}

		return $this->createJsonResponse(array(
			'success' => true
		));
	}

	public function addCategoryAction($type)
	{
		$cat = PublishCategoryEdit::addCategory($type, $this->in->getString('title'));

		switch ($type) {
			case 'articles':   $url = $this->generateUrl('agent_kb_list', array('category_id' => $cat->id)); break;
			case 'downloads':  $url = $this->generateUrl('agent_downloads_list', array('category_id' => $cat->id)); break;
			case 'news':       $url = $this->generateUrl('agent_news_list', array('category_id' => $cat->id)); break;
			case 'feedback':   $url = $this->generateUrl('agent_feedback_category', array('category_id' => $cat->id)); break;
		}

		return $this->createJsonResponse(array(
			'success' => true,
			'id' => $cat['id'],
			'url' => $url
		));
	}

	public function deleteCategoryAction($type)
	{
		try {
			PublishCategoryEdit::deleteCategory($type, $this->in->getUint('category_id'));
		} catch (\OutOfBoundsException $e) {
			return $this->createJsonResponse(array(
				'error'       => true,
				'error_code'  => 'not_empty',
				'category_id' => $this->in->getUint('category_id'),
				'type'        => $type,
			));
		}

		return $this->createJsonResponse(array(
			'success' => true,
			'category_id' => $this->in->getUint('category_id'),
			'type' => $type
		));
	}

	############################################################################
	# search
	############################################################################

	public function searchAction()
	{
		$query = $this->in->getString('query');
		$types = $this->container->getIn()->getCleanValueArray('types');

		$result_set = $this->container->getSearchAdapter()->getContentSearcher()->query($query, 250, 1, $types);
		$results    = $this->container->getSearchAdapter()->getResultSetObjects($result_set, true);

		return $this->render('AgentBundle:Publish:search-results.html.twig', array(
			'results'           => $results,
		));
	}
}

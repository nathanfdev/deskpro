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
 * @subpackage ApiBundle
 */

namespace Application\ApiBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsComment;
use Application\DeskPRO\Searcher\NewsSearch;
use Application\DeskPRO\UI\RuleBuilder;

use Application\AgentBundle\Controller\Helper\NewsResults;
use Application\DeskPRO\ContentRevision\Util as ContentRevisionUtil;

use Application\DeskPRO\ContentSearch\RelatedContentFinder;
use Application\DeskPRO\Publish\RelatedContentUpdate;

class NewsController extends AbstractController
{
	public function searchAction()
	{
		$search_map = array(
			'category' => NewsSearch::TERM_CATEGORY,
			'category_specific' => NewsSearch::TERM_CATEGORY_SPECIFIC,
			'label' => NewsSearch::TERM_LABEL,
			'status' => NewsSearch::TERM_STATUS
		);

		$terms = array();

		foreach ($search_map AS $input => $search_key) {
			$value = $this->in->getCleanValueArray($input, 'raw', 'discard');
			if ($value) {
				$terms[] = array('type' => $search_key, 'op' => 'contains', 'options' => $value);
			}
		}

		$date_created_start = $this->in->getUint('date_created_start');
		$date_created_end = $this->in->getUint('date_created_end');
		if ($date_created_end) {
			$terms[] = array('type' => NewsSearch::TERM_DATE_CREATED, 'op' => 'between', 'options' => array(
				'date1' => $date_created_start,
				'date2' => $date_created_end
			));
		} else if ($date_created_start) {
			$terms[] = array('type' => NewsSearch::TERM_DATE_CREATED, 'op' => 'between', 'options' => array(
				'date1' => $date_created_start
			));
		}

		$order_by = $this->in->getString('order');
		if (!$order_by) {
			$order_by = 'date:desc';
		}

		$extra = array();
		if ($order_by !== null) {
			$extra['order_by'] = $order_by;
		}

		if ($this->in->checkIsset('cache')) {
			$cache = $this->in->getUint('cache');
		} else {
			$cache = 3600;
		}

		$result_cache = $this->getApiSearchResult($terms, $extra, $cache, new NewsSearch());

		$page = $this->in->getUint('page');
		if (!$page) $page = 1;

		$per_page = 25;

		$ids = $result_cache->results;

		$page_ids = \Orb\Util\Arrays::getPageChunk($ids, $page, $per_page);
		$news = App::getEntityRepository('DeskPRO:News')->getByIds($page_ids, true);

		return $this->createApiResponse(array(
			'page' => $page,
			'per_page' => $per_page,
			'total' => count($ids),
			'news' => $this->getApiData($news)
		));
	}

	public function newNewsAction()
	{
		$errors = array();
		$news = new News();

		$title = $this->in->getString('title');
		if ($title) {
			$news->title = $title;
		} else {
			$errors['title'] = array('required_field.title', 'title is required');
		}

		$content = $this->in->getHtml('content');
		if ($content) {
			$news->content = $content;
		} else {
			$errors['content'] = array('required_field.content', 'content is required');
		}

		$status = $this->in->getString('status');
		if (!$status) {
			$status = 'published';
		}
		$news->setStatusCode($status);

		$cat = $this->em->find('DeskPRO:NewsCategory', $this->in->getUint('category_id'));
		if (!$cat) {
			$errors['category_id'] = array('invalid_argument.category_id', 'category_id not found');
		} else {
			$news->category = $cat;
		}

		if ($errors) {
			return $this->createApiMultipleErrorResponse($errors);
		}

		$news->person = $this->person;

		$this->em->persist($news);
		$this->em->flush();

		$labels = $this->in->getCleanValueArray('label', 'string', 'discard');
		if ($labels) {
			$news->getLabelManager()->setLabelsArray($labels, $this->em);
			$this->em->flush();
		}

		return $this->createApiCreateResponse(
			array('id' => $news->id),
			$this->generateUrl('api_news_news', array('news_id' => $news->id), true)
		);
	}

	public function getNewsAction($news_id)
	{
		$news = $this->_getNewsOr404($news_id);

		return $this->createApiResponse(array('news' => $news->toApiData()));
	}

	public function postNewsAction($news_id)
	{
		$news = $this->_getNewsOr404($news_id, 'edit');

		$revs = array();

		$title = $this->in->getString('title');
		if ($title) {
			$news->title = $title;

			$rev = ContentRevisionUtil::findOrCreate($news, 'title', $this->person);
			$rev->title = $news->title;

			$revs['title'] = $rev;
		}

		$status = $this->in->getString('status');
		if ($status) {
			$news->status = $status;
		}

		$content = $this->in->getString('content');
		if ($content && $content != $news->content) {
			$news->content = $this->in->getHtml('content');

			$rev = ContentRevisionUtil::findOrCreate($news, array('content'), $this->person);
			$rev->content = $news->content;

			$revs['content'] = $rev;
		}

		$category_id = $this->in->getUint('category_id');
		if ($category_id) {
			$cat = $this->em->find('DeskPRO:NewsCategory', $category_id);
			if ($cat) {
				$news->category = $cat;
			}
		}

		foreach ($revs AS $rev) {
			$this->em->persist($rev);
		}
		$this->em->persist($news);
		$this->em->flush();

		return $this->createSuccessResponse();
	}

	public function deleteNewsAction($news_id)
	{
		$news = $this->_getNewsOr404($news_id, 'delete');

		$news->status_code = 'hidden.deleted';
		$this->em->persist($news);
		$this->em->flush();

		return $this->createSuccessResponse();
	}

	public function getNewsLabelsAction($news_id)
	{
		$news = $this->_getNewsOr404($news_id);

		return $this->createApiResponse(array('labels' => $this->getApiData($news->labels)));
	}

	public function postNewsLabelsAction($news_id)
	{
		$news = $this->_getNewsOr404($news_id, 'edit');
		$label = $this->in->getString('label');

		if ($label === '') {
			return $this->createApiErrorResponse('required_field', "Field 'label' missing or empty");
		}

		$news->getLabelManager()->addLabel($label);
		$this->em->persist($news);
		$this->em->flush();

		return $this->createApiCreateResponse(
			array('label' => $label),
			$this->generateUrl('api_news_news_label', array('news_id' => $news->id, 'label' => $label), true)
		);
	}

	public function getNewsLabelAction($news_id, $label)
	{
		$news = $this->_getNewsOr404($news_id);

		if ($news->getLabelManager()->hasLabel($label)) {
			return $this->createApiResponse(array('exists' => true));
		} else {
			return $this->createApiResponse(array('exists' => false));
		}
	}

	public function deleteNewsLabelAction($news_id, $label)
	{
		$news = $this->_getNewsOr404($news_id, 'edit');

		$news->getLabelManager()->removeLabel($label);
		$this->em->persist($news);
		$this->em->flush();

		return $this->createSuccessResponse();
	}

	public function getCategoriesAction()
	{
		$categories = $this->em->getRepository('DeskPRO:NewsCategory')->getFlatHierarchy();

		return $this->createApiResponse(array('categories' => $categories));
	}

	/**
	 * @param integer $id
	 * @return \Application\DeskPRO\Entity\News
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	protected function _getNewsOr404($id, $check_perm = false)
	{
		$news = $this->em->getRepository('DeskPRO:News')->findOneById($id);

		if (!$news) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no news with ID $id");
		}

		if ($check_perm) {
			if ($check_perm == 'edit' && !$this->person->PermissionsManager->PublishChecker->canEdit($news)) {
				throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
			}

			if ($check_perm == 'delete' && !$this->person->PermissionsManager->PublishChecker->canDelete($news)) {
				throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
			}
		}

		return $news;
	}
}

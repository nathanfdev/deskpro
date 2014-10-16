<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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
use Application\DeskPRO\ContentRevision\Util as ContentRevisionUtil;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleComment;
use Application\DeskPRO\Searcher\ArticleSearch;
use Orb\Util\Numbers;

/**
 * @SWG\Resource(
 * 	resourcePath="/kb",
 * 	description="Operations about Knowledgebase",
 * 	basePath="/api"
 * )
 */
class KbController extends AbstractController
{
	/**
	 * @SWG\Api(
	 * 	path="/kb",
	 * 	@SWG\Operation(
	 * 		method="GET",
	 * 		summary="Search for articles matching criteria",
	 * 		notes="Returns list of articles that matched.",
	 *		type="array",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="category_id[]",
	 *				description="Comma seperated IDs of categories to search in",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="category_id_specific[]",
	 *				description="Comma seperated IDs of categories to search in",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="date_created_end",
	 *				description="Requires the article to have been created before this date. Must be specified as a Unix timestamp.",
	 *				paramType="query",
	 *				required=false,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="date_created_start",
	 *				description="Requires the article to have been created after this date. Must be specified as a Unix timestamp.",
	 *				paramType="query",
	 *				required=false,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="label[]",
	 *				description="Requires the article to have this label.",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="status[]",
	 *				description="Requires the article to be in this status. Possible values include: published, archived, hidden.",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="new",
	 *				description="If non-0, requires the article to be considered new (created within the last month). If 0, this does nothing.",
	 *				paramType="query",
	 *				required=false,
	 *				type="boolean"
	 *			),
	 *			@SWG\Parameter(
	 *				name="popular",
	 *				description="If non-0, requires the article to be considered popular (50 or more articles). If 0, this does nothing.",
	 *				paramType="query",
	 *				required=false,
	 *				type="boolean"
	 *			),
	 *			@SWG\Parameter(
	 *				name="order",
	 *				description="Order of the results. Defaults to the publishing date.",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="cache_id",
	 *				description="If provided, cached results from this result set are used. If it cannot be found or used, the other constraints provided will be used to create a new result set.",
	 *				paramType="query",
	 *				required=false,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="page",
	 *				description="The page number of the results to fetch.",
	 *				paramType="query",
	 *				required=false,
	 *				type="integer"
	 *			)
	 *		)
	 * 	)
	 * )
	 */
	public function searchAction()
	{
		$search_map = array(
			'category_id' => ArticleSearch::TERM_CATEGORY,
			'category_id_specific' => ArticleSearch::TERM_CATEGORY_SPECIFIC,
			'label' => ArticleSearch::TERM_LABEL,
			'new' => ArticleSearch::TERM_NEW,
			'popular' => ArticleSearch::TERM_POPULAR,
			'status' => ArticleSearch::TERM_STATUS
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
			$terms[] = array('type' => ArticleSearch::TERM_DATE_CREATED, 'op' => 'between', 'options' => array(
				'date1' => $date_created_start,
				'date2' => $date_created_end
			));
		} else if ($date_created_start) {
			$terms[] = array('type' => ArticleSearch::TERM_DATE_CREATED, 'op' => 'between', 'options' => array(
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

		$result_cache = $this->getApiSearchResult('article', $terms, $extra, $this->in->getUint('cache_id'), new ArticleSearch());

		$page = $this->in->getUint('page');
		if (!$page) $page = 1;

		$per_page = Numbers::bound($this->in->getUint('per_page') ?: 25, 1, 250);

		$ids = $result_cache->results;

		$page_ids = \Orb\Util\Arrays::getPageChunk($ids, $page, $per_page);
		$articles = App::getEntityRepository('DeskPRO:Article')->getByIds($page_ids, true);

		return $this->createApiResponse(array(
			'page' => $page,
			'per_page' => $per_page,
			'total' => count($ids),
			'cache_id' => $result_cache->id,
			'articles' => $this->getApiData($articles)
		));
	}

	/**
	 * @SWG\Api(
	 * 	path="/kb",
	 * 	@SWG\Operation(
	 * 		method="POST",
	 * 		summary="Creates a new article.",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="title",
	 *				description="Title of the article. ",
	 *				paramType="query",
	 *				required=true,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="content",
	 *				description="Content of the article. Marked up using HTML.",
	 *				paramType="query",
	 *				required=true,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="category_id[]",
	 *				description="ID of the category the article is in. At least one is required.",
	 *				paramType="query",
	 *				required=false,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="label[]",
	 *				description="Comma seperated list of Labels to apply to the article.",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="status",
	 *				description="Status of the article. Defaults to new if not overridden by this or status_category_id.",
	 *				paramType="query",
	 *				required=false,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="attach[]",
	 *				description="Attached file that represents the article. See the <a href='https://support.deskpro.com/articles/articles/88-api-basics'>API Basics</a> for more information on sending files to the API. Required if no attach_id is provided.",
	 *				paramType="body",
	 *				required=true,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="attach_id[]",
	 *				description="The ID of an already uploaded file to include with the article. Required if no attach value is provided.",
	 *				paramType="query",
	 *				required=false,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="date",
	 *				description="If provided, changes the creation/publishing date of the article to the given Unix timestamp. If not provided, defaults to the current time.",
	 *				paramType="query",
	 *				required=false,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="date_end",
	 *				description="If provided, sets the Unix timestamp when an action should be taken.",
	 *				paramType="query",
	 *				required=false,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="date_published",
	 *				description="If provided and the article is not published, sets the Unix timestamp when an article should be published.",
	 *				paramType="query",
	 *				required=false,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="end_action",
	 *				description="If provided with a date_end, the action that should be taken when date_end is reached. Possible values are delete or archive.",
	 *				paramType="query",
	 *				required=false,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="field[]",
	 *				description="Value for the specified custom field.",
	 *				paramType="query",
	 *				required=false,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="product_id[]",
	 *				description="ID of product this article is associated with.",
	 *				paramType="query",
	 *				required=false,
	 *				type="integer"
	 *			)
	 *		)
	 * 	)
	 * )
	 */
	public function newArticleAction()
	{
		$errors = array();
		$article = new Article();

		$set_title    = null;
		$set_content  = null;
		$title_lang   = array();
		$content_lang = array();

		if (is_array($_POST['title']) && is_array($_POST['content'])) {

			foreach ($this->container->getLanguageData()->getAll() as $lang) {
				$lang_id = $lang->getId();

				$title       = $this->in->getString("title.$lang_id");
				$content_val = (string)$this->in->getRaw("content.$lang_id");

				if ($lang_id == $article->language->getId()) {
					$set_title   = $title;
					$set_content = $content_val;
					continue;
				}

				if (!$title && !$content_val) {
					continue;
				}

				$title_lang[$lang->getId()] = $title;
				$content_lang[$lang->getId()] = $content_val;
			}

		} else {
			$set_title   = $this->in->getString('title');
			$set_content = (string)$this->in->getRaw('content');
		}

		if ($set_title) {
			$article->title = $set_title;
		} else {
			$errors['title'] = array('required_field.title', 'title is required');
		}

		if ($set_content) {
			$article->content = $set_content;
		} else {
			$errors['content'] = array('required_field.content', 'content is required');
		}

		$status = $this->in->getString('status');
		if (!$status) {
			$status = 'published';
		}
		$article->setStatusCode($status);

		$date = $this->in->getUint('date');
		if ($date) {
			$article->date_created = new \DateTime('@' . $date);
			if ($status == 'published') {
				$article->date_published = new \DateTime('@' . $date);
			}
		}

		if ($this->in->checkIsset('date_published') && $status != 'published') {
			$date_published = $this->in->getUint('date_published');
			if ($date_published) {
				$article->date_published = new \DateTime('@' . $date_published);
			}
		}

		$date_end = $this->in->getUint('date_end');
		if ($date_end) {
			$article->date_end = new \DateTime('@' . $date_end);
			$article->end_action = $this->in->getString('end_action') ?: Article::END_ACTION_DELETE;
		}

		$cat_ids = $this->in->getCleanValueArray('category_id', 'uint', 'discard');
		$cats = $this->em->getRepository('DeskPRO:ArticleCategory')->getByIds($cat_ids);
		if (!$cats) {
			$errors['category_id'] = array('invalid_argument.category_id', 'no categories found');
		}
		$article->setCategories($cats);

		$product_ids = $this->in->getCleanValueArray('product_id', 'uint', 'discard');
		$products = $this->em->getRepository('DeskPRO:Product')->getByIds($product_ids);
		if ($products) {
			$article->setProducts($products);
		}

		if ($errors) {
			return $this->createApiMultipleErrorResponse($errors);
		}

		$this->_insertArticleAttachments($article);
		$article->person = $this->person;

		$this->em->persist($article);
		$this->em->flush();

		$field_manager = $this->container->getSystemService('article_fields_manager');
		$post_custom_fields = $this->getCustomFieldInput();
		if (!empty($post_custom_fields)) {
			$field_manager->saveFormToObject($post_custom_fields, $article, true);
		}

		$labels = $this->in->getCleanValueArray('label', 'string', 'discard');
		if ($labels) {
			$article->getLabelManager()->setLabelsArray($labels, $this->em);
		}

		$this->em->flush();

		// Set other langs
		foreach ($title_lang as $lang_id => $title) {
			$lang = $this->container->getLanguageData()->get($lang_id);
			$content_val = $content_lang[$lang_id];

			$rec = $this->container->getObjectLangRepository()->setRec($lang, $article, 'title', $title);
			$this->em->persist($rec);

			$rec = $this->container->getObjectLangRepository()->setRec($lang, $article, 'content', $content_val);
			$this->em->persist($rec);
		}

		$this->em->flush();

		return $this->createApiCreateResponse(
			array('id' => $article->id),
			$this->generateUrl('api_kb_article', array('article_id' => $article->id), true)
		);
	}

	/**
	 * @SWG\Api(
	 * 	path="/kb/{article_id}",
	 * 	@SWG\Operation(
	 * 		method="GET",
	 * 		summary="Gets a article by article ID.",
	 * 		notes="Information about the article by article ID.",
	 *		type="Article",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="article_id",
	 *				description="ID of the article that needs to be searched.",
	 *				paramType="path",
	 *				required=true,
	 *				type="integer"
	 *			)
	 *		),
	 *		@SWG\ResponseMessage(code=404, message="Article not found")
	 * 	)
	 * )
	 */
	public function getArticleAction($article_id)
	{
		$article = $this->_getArticleOr404($article_id);

		return $this->createApiResponse(array('article' => $article->toApiData()));
	}

	/**
	 * @SWG\Api(
	 * 	path="/kb/{article_id}",
	 * 	@SWG\Operation(
	 * 		method="POST",
	 * 		summary="Updates an article.",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="article_id",
	 *				description="ID of the article that needs to be upated ",
	 *				paramType="path",
	 *				required=true,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="title",
	 *				description="Title of the article. ",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="content",
	 *				description="Content of the article. Marked up using HTML.",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="category_id[]",
	 *				description="ID of the category the article is in. At least one is required.",
	 *				paramType="query",
	 *				required=false,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="status",
	 *				description="Status of the article. Defaults to new if not overridden by this or status_category_id.",
	 *				paramType="query",
	 *				required=false,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="attach[]",
	 *				description="Attached file that represents the article. See the <a href='https://support.deskpro.com/articles/articles/88-api-basics'>API Basics</a> for more information on sending files to the API. Required if no attach_id is provided.",
	 *				paramType="body",
	 *				required=true,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="attach_id[]",
	 *				description="The ID of an already uploaded file to include with the article. Required if no attach value is provided.",
	 *				paramType="query",
	 *				required=false,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="date",
	 *				description="If provided, changes the creation/publishing date of the article to the given Unix timestamp. If not provided, defaults to the current time.",
	 *				paramType="query",
	 *				required=false,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="date_end",
	 *				description="If provided, sets the Unix timestamp when an action should be taken.",
	 *				paramType="query",
	 *				required=false,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="date_published",
	 *				description="If provided and the article is not published, sets the Unix timestamp when an article should be published.",
	 *				paramType="query",
	 *				required=false,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="end_action",
	 *				description="If provided with a date_end, the action that should be taken when date_end is reached. Possible values are delete or archive.",
	 *				paramType="query",
	 *				required=false,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="field[]",
	 *				description="Value for the specified custom field.",
	 *				paramType="query",
	 *				required=false,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="product_id[]",
	 *				description="ID of product this article is associated with.",
	 *				paramType="query",
	 *				required=false,
	 *				type="integer"
	 *			)
	 *		)
	 * 	)
	 * )
	 */
	public function postArticleAction($article_id)
	{
		$article = $this->_getArticleOr404($article_id, 'edit');

		$lang_id = $this->in->getUint('language_id');
		$lang = null;
		if ($lang_id) {
			$lang = $this->container->getLanguageData()->get($lang_id);
		}
		if ($lang) {
			$article->language = $lang;
		}

		$revs = array();

		if (is_array($_POST['title']) && is_array($_POST['content'])) {

			$set_title   = null;
			$set_content = null;

			foreach ($this->container->getLanguageData()->getAll() as $lang) {
				$lang_id = $lang->getId();

				$title       = $this->in->getString("title.$lang_id");
				$content_val = (string)$this->in->getRaw("content.$lang_id");

				if ($lang_id == $article->language->getId()) {
					$set_title   = $title;
					$set_content = $content_val;
					continue;
				}

				if (!$title && !$content_val) {
					continue;
				}

				$rec = $this->container->getObjectLangRepository()->setRec($lang, $article, 'title', $title);
				$this->em->persist($rec);

				$rec = $this->container->getObjectLangRepository()->setRec($lang, $article, 'content', $content_val);
				$this->em->persist($rec);
			}
		} else {
			$set_title   = $this->in->getString('title');
			$set_content = (string)$this->in->getRaw('content');
		}

		if ($set_title && $set_title != $article->title) {
			$article->title = $set_title;

			$rev = ContentRevisionUtil::findOrCreate($article, 'title', $this->person);
			$rev->title = $article->title;

			$revs['title'] = $rev;
		}

		if ($set_content && $set_content != $article->content) {
			$article->content = $set_content;

			$rev = ContentRevisionUtil::findOrCreate($article, array('content'), $this->person);
			$rev->content = $article->content;

			$revs['content'] = $rev;
		}

		$status = $this->in->getString('status');
		if ($status) {
			$article->setStatusCode($status);
		}

		$cat_ids = $this->in->getCleanValueArray('category_id', 'uint', 'discard');
		$cats = $this->em->getRepository('DeskPRO:ArticleCategory')->getByIds($cat_ids);
		if ($cats) {
			$article->setCategories($cats);
		}

		$product_ids = $this->in->getCleanValueArray('product_id', 'uint', 'discard');
		$products = $this->em->getRepository('DeskPRO:Product')->getByIds($product_ids);
		if ($products) {
			$article->setProducts($products);
		} else if ($this->in->getBool('remove_product')) {
			$article->setProducts(array());
		}

		if ($this->in->checkIsset('date_published') && $article->status != 'published') {
			$date_published = $this->in->getUint('date_published');
			if ($date_published) {
				$article->date_published = new \DateTime('@' . $date_published);
			} else {
				$article->date_published = null;
			}
		}

		if ($this->in->checkIsset('date_end')) {
			$date_end = $this->in->getUint('date_end');
			if ($date_end) {
				$article->date_end = new \DateTime('@' . $date_end);
				$article->end_action = $this->in->getString('end_action') ?: Article::END_ACTION_DELETE;
			} else {
				$article->date_end = null;
				$article->end_action = null;
			}
		}

		$this->_insertArticleAttachments($article);

		foreach ($revs AS $rev) {
			$this->em->persist($rev);
		}
		$this->em->persist($article);

		$field_manager = $this->container->getSystemService('article_fields_manager');
		$post_custom_fields = $this->getCustomFieldInput();
		if (!empty($post_custom_fields)) {
			$field_manager->saveFormToObject($post_custom_fields, $article, true);
		}

		$this->em->flush();

		return $this->createSuccessResponse();
	}

	protected function _insertArticleAttachments(Article $article)
	{
		$attachments = $this->request->files->get('attach');
		if (!is_array($attachments)) {
			$attachments = array($attachments);
		}
		$accept = $this->container->getAttachmentAccepter();

		foreach ($attachments AS $file) {
			$error = $accept->getError($file, 'agent');
			if (!$error) {
				$blob = $accept->accept($file);
				$this->_addArticleAttachment($blob, $article);
			}
		}

		foreach ($this->in->getCleanValueArray('attach_id') as $blob_id) {
			$this->_addArticleAttachment($blob_id, $article);
		}
	}

	protected function _addArticleAttachment($blob_id, Article $article)
	{
		if ($blob_id instanceof \Application\DeskPRO\Entity\Blob) {
			$blob = $blob_id;
		} else {
			$blob = $this->em->getRepository('DeskPRO:Blob')->find($blob_id);
		}

		if ($blob) {
			$attach = new \Application\DeskPRO\Entity\ArticleAttachment();
			$attach['blob'] = $blob;
			$attach['person'] = $this->person;

			$article->addAttachment($attach);

			return $attach;
		} else {
			return false;
		}
	}

	/**
	 * @SWG\Api(
	 * 	path="/kb/{article_id}",
	 * 	@SWG\Operation(
	 * 		method="DELETE",
	 * 		summary="DELETES a article by article ID.",
	 * 		notes="DELETES the article by article ID.",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="article_id",
	 *				description="ID of the article that needs to be deleted.",
	 *				paramType="path",
	 *				required=true,
	 *				type="integer"
	 *			)
	 *		),
	 *		@SWG\ResponseMessage(code=404, message="Article not found")
	 * 	)
	 * )
	 */
	public function deleteArticleAction($article_id)
	{
		$article = $this->_getArticleOr404($article_id, 'delete');

		$article->status_code = 'hidden.deleted';
		$this->em->persist($article);
		$this->em->flush();

		return $this->createSuccessResponse();
	}

	/**
	 * @SWG\Api(
	 * 	path="/kb/{article_id}/votes",
	 * 	@SWG\Operation(
	 * 		method="GET",
	 * 		summary="Gets the votes for an article.",
	 * 		notes="Information about the votes on an article by article ID.",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="article_id",
	 *				description="ID of the article that needs to be searched.",
	 *				paramType="path",
	 *				required=true,
	 *				type="integer"
	 *			)
	 *		),
	 *		@SWG\ResponseMessage(code=404, message="Article not found")
	 * 	)
	 * )
	 */
	public function getArticleVotesAction($article_id)
	{
		$article = $this->_getArticleOr404($article_id);
		$votes = App::getEntityRepository('DeskPRO:Rating')->getRatingsFor('article', $article->id);

		return $this->createApiResponse(array('votes' => $this->getApiData($votes)));
	}

	/**
	 * @SWG\Api(
	 * 	path="/kb/{article_id}/comments",
	 * 	@SWG\Operation(
	 * 		method="GET",
	 * 		summary="Gets the comments for an article.",
	 * 		notes="Information about the comments on an article by article ID.",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="article_id",
	 *				description="ID of the article that needs to be searched.",
	 *				paramType="path",
	 *				required=true,
	 *				type="integer"
	 *			)
	 *		),
	 *		@SWG\ResponseMessage(code=404, message="Article not found")
	 * 	)
	 * )
	 */
	public function getArticleCommentsAction($article_id)
	{
		$article = $this->_getArticleOr404($article_id);
		$comments = $this->em->getRepository('DeskPRO:ArticleComment')->getComments($article);

		return $this->createApiResponse(array('comments' => $this->getApiData($comments)));
	}

	/**
	 * @SWG\Api(
	 * 	path="/kb/{article_id}/comments",
	 * 	@SWG\Operation(
	 * 		method="POST",
	 * 		summary="Add a comment for an article.",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="article_id",
	 *				description="ID of the article that needs to be searched.",
	 *				paramType="path",
	 *				required=true,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="content",
	 *				description="Text of the comment.",
	 *				paramType="path",
	 *				required=true,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="person_id",
	 *				description="ID of the person that owns the comment. If not provided, defaults to the agent making the request.",
	 *				paramType="path",
	 *				required=false,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="status",
	 *				description="Status of the comment. Defaults to visible.",
	 *				paramType="path",
	 *				required=false,
	 *				type="string"
	 *			)
	 *		),
	 *		@SWG\ResponseMessage(code=404, message="Article not found")
	 * 	)
	 * )
	 */
	public function newArticleCommentAction($article_id)
	{
		$article = $this->_getArticleOr404($article_id);

		$content = $this->in->getString('content');
		if (!$content) {
			return $this->createApiErrorResponse('required_field.content', 'Missing content');
		}

		$person_id = $this->in->getUint('person_id');
		$person = null;
		if ($person_id) {
			$person = $this->em->getRepository('DeskPRO:Person')->find($person_id);
		}

		$status = $this->in->getString('status');

		$comment = new ArticleComment();
		$comment->article = $article;
		$comment->person = $person ?: $this->person;
		$comment['content'] = $content;
		$comment['status'] = $status ?: 'visible';
		$comment['is_reviewed'] = ($comment['status'] == 'visible' && !$person);
		$comment['date_created']  = new \DateTime();

		$this->em->persist($comment);
		$this->em->flush();

		return $this->createApiCreateResponse(
			array('id' => $comment->id),
			$this->generateUrl('api_kb_article_comments_comment', array('article_id' => $article->id, 'comment_id' => $comment->id), true)
		);
	}

	/**
	 * @SWG\Api(
	 * 	path="/kb/{article_id}/comments/{comment_id}",
	 * 	@SWG\Operation(
	 * 		method="GET",
	 * 		summary="Gets info about a specific article comment.",
	 * 		notes="Information about a specific comments on an article by comment ID.",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="article_id",
	 *				description="ID of the article that needs to be searched.",
	 *				paramType="path",
	 *				required=true,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="comment_id",
	 *				description="ID of the article comment that needs to be searched.",
	 *				paramType="path",
	 *				required=true,
	 *				type="integer"
	 *			)
	 *		),
	 *		@SWG\ResponseMessage(code=404, message="Article not found")
	 * 	)
	 * )
	 */
	public function getArticleCommentAction($article_id, $comment_id)
	{
		$article = $this->_getArticleOr404($article_id);
		$comment = $this->em->getRepository('DeskPRO:ArticleComment')->find($comment_id);
		if (!$comment || $comment->article->id != $article->id) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		return $this->createApiResponse(array('comment' => $comment->toApiData()));
	}

	/**
	 * @SWG\Api(
	 * 	path="/kb/{article_id}/comments/{comment_id}",
	 * 	@SWG\Operation(
	 * 		method="POST",
	 * 		summary="Updates an article comment.",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="article_id",
	 *				description="ID of the article that needs to be searched.",
	 *				paramType="path",
	 *				required=true,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="comment_id",
	 *				description="ID of the article comment that needs to be updated.",
	 *				paramType="path",
	 *				required=true,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="content",
	 *				description="Text of the comment.",
	 *				paramType="path",
	 *				required=true,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="status",
	 *				description="Status of the comment. Defaults to visible.",
	 *				paramType="path",
	 *				required=false,
	 *				type="string"
	 *			)
	 *		),
	 *		@SWG\ResponseMessage(code=404, message="Article not found")
	 * 	)
	 * )
	 */
	public function postArticleCommentAction($article_id, $comment_id)
	{
		$article = $this->_getArticleOr404($article_id);
		$comment = $this->em->getRepository('DeskPRO:ArticleComment')->find($comment_id);
		if (!$comment || $comment->article->id != $article->id) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		$approved = false;
		$status = $this->in->getString('status');
		if ($status) {
			$approved = ($status == 'visible' && $comment->status != 'visible');
			$comment->status = $status;
		}

		$content = $this->in->getString('content');
		if ($content) {
			$comment->content = $content;
		}

		$this->em->persist($comment);
		$this->em->flush();

		if ($approved) {
			$this->_sendCommentApprovedNotification($comment);
		}

		return $this->createSuccessResponse();
	}

	/**
	 * @SWG\Api(
	 * 	path="/kb/{article_id}/comments/{comment_id}",
	 * 	@SWG\Operation(
	 * 		method="DELETE",
	 * 		summary="DELETEs an article comment.",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="article_id",
	 *				description="ID of the article that needs to be searched.",
	 *				paramType="path",
	 *				required=true,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="comment_id",
	 *				description="ID of the article comment that needs to be deleted.",
	 *				paramType="path",
	 *				required=true,
	 *				type="integer"
	 *			)
	 *		),
	 *		@SWG\ResponseMessage(code=404, message="Article not found")
	 * 	)
	 * )
	 */
	public function deleteArticleCommentAction($article_id, $comment_id)
	{
		$article = $this->_getArticleOr404($article_id);
		$comment = $this->em->getRepository('DeskPRO:ArticleComment')->find($comment_id);
		if (!$comment || $comment->article->id != $article->id) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		$this->em->remove($comment);
		$this->em->flush();

		$this->_sendCommentDeletedNotification($comment);

		return $this->createSuccessResponse();
	}

	/**
	 * @SWG\Api(
	 * 	path="/kb/{article_id}/attachments",
	 * 	@SWG\Operation(
	 * 		method="GET",
	 * 		summary="Gets the attachments for an article.",
	 * 		notes="Information about the attachments on an article by article ID.",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="article_id",
	 *				description="ID of the article that needs to be searched.",
	 *				paramType="path",
	 *				required=true,
	 *				type="integer"
	 *			)
	 *		),
	 *		@SWG\ResponseMessage(code=404, message="Article not found")
	 * 	)
	 * )
	 */
	public function getArticleAttachmentsAction($article_id)
	{
		$article = $this->_getArticleOr404($article_id);

		return $this->createApiResponse(array('attachments' => $this->getApiData($article->attachments)));
	}

	/**
	 * @SWG\Api(
	 * 	path="/kb/{article_id}/attachments",
	 * 	@SWG\Operation(
	 * 		method="POST",
	 * 		summary="Add an attachment for an article.",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="article_id",
	 *				description="ID of the article that needs to be searched.",
	 *				paramType="path",
	 *				required=true,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="attach",
	 *				description="Attached file that represents the article. See the <a href='https://support.deskpro.com/articles/articles/88-api-basics'>API Basics</a> for more information on sending files to the API. Required if no attach_id is provided.",
	 *				paramType="body",
	 *				required=true,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="attach_id",
	 *				description="The ID of an already uploaded file to include with the article. Required if no attach value is provided.",
	 *				paramType="query",
	 *				required=false,
	 *				type="integer"
	 *			)
	 *		),
	 *		@SWG\ResponseMessage(code=404, message="Article not found")
	 * 	)
	 * )
	 */
	public function newArticleAttachmentAction($article_id)
	{
		$article = $this->_getArticleOr404($article_id, 'edit');

		$file = $this->request->files->get('attach');
		if (is_array($file)) {
			$file = reset($file);
		}

		if ($file) {
			$accept = $this->container->getAttachmentAccepter();

			$error = $accept->getError($file, 'agent');
			if (!$error) {
				$blob = $accept->accept($file);
			} else {
				$message = $this->container->getTranslator()->phrase('agent.general.attach_error_' . $error['error_code'], $error);
				return $this->createApiErrorResponse($error['error_code'], $message);
			}
		} else {
			$blob_id = $this->in->getUint('attach_id');
			$blob = $this->em->find('DeskPRO:Blob', $blob_id);
			if (!$blob) {
				return $this->createApiErrorResponse('invalid_argument.attach_id', 'attach_id not found');
			}
		}

		$attach = $this->_addArticleAttachment($blob, $article);

		$this->em->persist($article);
		$this->em->flush();

		return $this->createApiCreateResponse(
			array('id' => $attach->id),
			$this->generateUrl('api_kb_article_attachment', array('article_id' => $article->id, 'attachment_id' => $attach->id), true)
		);
	}

	/**
	 * @SWG\Api(
	 * 	path="/kb/{article_id}/comments/{attachment_id}",
	 * 	@SWG\Operation(
	 * 		method="GET",
	 * 		summary="Gets info about a specific article attachment.",
	 * 		notes="Information about a specific attachment on an article by attachment ID.",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="article_id",
	 *				description="ID of the article that needs to be searched.",
	 *				paramType="path",
	 *				required=true,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="attachment_id",
	 *				description="ID of the article attachment that needs to be searched.",
	 *				paramType="path",
	 *				required=true,
	 *				type="integer"
	 *			)
	 *		),
	 *		@SWG\ResponseMessage(code=404, message="Article not found")
	 * 	)
	 * )
	 */
	public function getArticleAttachmentAction($article_id, $attachment_id)
	{
		$article = $this->_getArticleOr404($article_id);
		$exists = false;
		foreach ($article->attachments AS $attachment) {
			if ($attachment->id == $attachment_id) {
				$exists = true;
				break;
			}
		}

		return $this->createApiResponse(array('exists' => $exists));
	}

	/**
	 * @SWG\Api(
	 * 	path="/kb/{article_id}/comments/{attachment_id}",
	 * 	@SWG\Operation(
	 * 		method="DELETE",
	 * 		summary="DELETEs info about a specific article attachment.",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="article_id",
	 *				description="ID of the article that needs to be searched.",
	 *				paramType="path",
	 *				required=true,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="attachment_id",
	 *				description="ID of the article attachment that needs to be deleted.",
	 *				paramType="path",
	 *				required=true,
	 *				type="integer"
	 *			)
	 *		),
	 *		@SWG\ResponseMessage(code=404, message="Article not found")
	 * 	)
	 * )
	 */
	public function deleteArticleAttachmentAction($article_id, $attachment_id)
	{
		$article = $this->_getArticleOr404($article_id);
		foreach ($article->attachments AS $k => $attachment) {
			if ($attachment->id == $attachment_id) {
				$article->attachments->remove($k);
				$this->em->remove($attachment);
				break;
			}
		}

		$this->em->persist($article);
		$this->em->flush();

		return $this->createSuccessResponse();
	}

	/**
	 * @SWG\Api(
	 * 	path="/kb/{article_id}/labels",
	 * 	@SWG\Operation(
	 * 		method="GET",
	 * 		summary="Gets the labels for an article.",
	 * 		notes="Information about the labels on an article by article ID.",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="article_id",
	 *				description="ID of the article that needs to be searched.",
	 *				paramType="path",
	 *				required=true,
	 *				type="integer"
	 *			)
	 *		),
	 *		@SWG\ResponseMessage(code=404, message="Article not found")
	 * 	)
	 * )
	 */
	public function getArticleLabelsAction($article_id)
	{
		$article = $this->_getArticleOr404($article_id);

		return $this->createApiResponse(array('labels' => $this->getApiData($article->labels)));
	}

	/**
	 * @SWG\Api(
	 * 	path="/kb/{article_id}/labels",
	 * 	@SWG\Operation(
	 * 		method="POST",
	 * 		summary="Add a label for an article.",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="article_id",
	 *				description="ID of the article that needs to be searched.",
	 *				paramType="path",
	 *				required=true,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="label",
	 *				description=" Label to add.",
	 *				paramType="query",
	 *				required=true,
	 *				type="string"
	 *			)
	 *		),
	 *		@SWG\ResponseMessage(code=404, message="Article not found")
	 * 	)
	 * )
	 */
	public function postArticleLabelsAction($article_id)
	{
		$article = $this->_getArticleOr404($article_id, 'edit');
		$label = $this->in->getString('label');

		if ($label === '') {
			return $this->createApiErrorResponse('required_field', "Field 'label' missing or empty");
		}

		$article->getLabelManager()->addLabel($label);
		$this->em->persist($article);
		$this->em->flush();

		return $this->createApiCreateResponse(
			array('label' => $label),
			$this->generateUrl('api_kb_article_label', array('article_id' => $article->id, 'label' => $label), true)
		);
	}

	/**
	 * @SWG\Api(
	 * 	path="/kb/{article_id}/labels/{label}",
	 * 	@SWG\Operation(
	 * 		method="GET",
	 * 		summary="Determines if an article has a label.",
	 * 		notes="Information about a specific label on an article by attachment ID.",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="article_id",
	 *				description="ID of the article that needs to be searched.",
	 *				paramType="path",
	 *				required=true,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="label",
	 *				description="label that needs to be searched.",
	 *				paramType="path",
	 *				required=true,
	 *				type="integer"
	 *			)
	 *		),
	 *		@SWG\ResponseMessage(code=404, message="Article not found")
	 * 	)
	 * )
	 */
	public function getArticleLabelAction($article_id, $label)
	{
		$article = $this->_getArticleOr404($article_id);

		if ($article->getLabelManager()->hasLabel($label)) {
			return $this->createApiResponse(array('exists' => true));
		} else {
			return $this->createApiResponse(array('exists' => false));
		}
	}

	/**
	 * @SWG\Api(
	 * 	path="/kb/{article_id}/labels/{label}",
	 * 	@SWG\Operation(
	 * 		method="DELETE",
	 * 		summary="Removes a label from an article.",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="article_id",
	 *				description="ID of the article that needs to be searched.",
	 *				paramType="path",
	 *				required=true,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="label",
	 *				description="label that needs to be deleted.",
	 *				paramType="path",
	 *				required=true,
	 *				type="integer"
	 *			)
	 *		),
	 *		@SWG\ResponseMessage(code=404, message="Article not found")
	 * 	)
	 * )
	 */
	public function deleteArticleLabelAction($article_id, $label)
	{
		$article = $this->_getArticleOr404($article_id, 'edit');

		$article->getLabelManager()->removeLabel($label);
		$this->em->persist($article);
		$this->em->flush();

		return $this->createSuccessResponse();
	}

	/**
	 * @SWG\Api(
	 * 	path="/kb/validating-comments",
	 * 	@SWG\Operation(
	 * 		method="GET",
	 * 		summary="Gets article comments that are awaiting validation."
	 * 	)
	 * )
	 */
	public function getValidatingCommentsAction()
	{
		$comments = $this->em->getRepository('DeskPRO:ArticleComment')->getValidatingComments();
		$entity_key = 'article';
		$output = array();
		foreach ($comments AS $key => $value) {
			$output[$key] = $value->toApiData(false, true);
			if ($value->$entity_key) {
				$output[$key][$entity_key] = $value->$entity_key->toApiData(false, false);
			}
		}

		return $this->createApiResponse(array('comments' => $output));
	}

	/**
	 * @SWG\Api(
	 * 	path="/kb/categories",
	 * 	@SWG\Operation(
	 * 		method="GET",
	 * 		summary="Gets available article categories."
	 * 	)
	 * )
	 */
	public function getCategoriesAction()
	{
		$categories = $this->em->getRepository('DeskPRO:ArticleCategory')->getFlatHierarchy();

		return $this->createApiResponse(array('categories' => $categories));
	}

	/**
	 * @SWG\Api(
	 * 	path="/kb/categories",
	 * 	@SWG\Operation(
	 * 		method="POST",
	 * 		summary="Creates an article category.",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="title",
	 *				description="Title of the category.",
	 *				paramType="query",
	 *				required=true,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="parent_id",
	 *				description="ID of the category's parent. Use 0 for no parent.",
	 *				paramType="query",
	 *				required=false,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="display_order",
	 *				description="Order of display of categories. Lower numbers will be displayed first.",
	 *				paramType="query",
	 *				required=false,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="usergroup_id[]",
	 *				description="ID of user group that has access. If not provided, defaults to all users.",
	 *				paramType="query",
	 *				required=false,
	 *				type="integer"
	 *			)
	 *		)
	 * 	)
	 * )
	 */
	public function postCategoriesAction()
	{
		$errors = array();

		$title = $this->in->getString('title');
		if (!$title) {
			$errors['title'] = array('required_field.title', 'title empty or missing');
		}

		$category = new \Application\DeskPRO\Entity\ArticleCategory();

		$category->title = $title;

		$parent_id = $this->in->getUint('parent_id');
		if ($parent_id) {
			$parent = $this->em->getRepository('DeskPRO:ArticleCategory')->find($parent_id);
			if ($parent) {
				$category->setParent($parent);
			}
		}

		$category->display_order = $this->in->getUint('display_order');

		if ($errors) {
			return $this->createApiMultipleErrorResponse($errors);
		}

		if ($this->in->checkIsset('usergroup_id')) {
			$usergroup_ids = $this->in->getCleanValueArray('usergroup_id', 'uint');
		} else {
			$usergroup_ids = array($this->container->getUserGroups()->getEveryoneGroup()->getId());
		}

		$this->db->beginTransaction();

		try {
			$this->em->persist($category);
			$this->em->flush();

			foreach ($usergroup_ids AS $usergroup_id) {
				if (!$usergroup_id) {
					continue;
				}
				App::getDb()->insert('article_category2usergroup', array(
					'category_id'  => $category->getId(),
					'usergroup_id' => $usergroup_id
				));
			}

			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}

		return $this->createApiCreateResponse(
			array('id' => $category->id),
			$this->generateUrl('api_kb_category', array('category_id' => $category->id), true)
		);
	}

	/**
	 * @SWG\Api(
	 * 	path="/kb/categories/{category_id}",
	 * 	@SWG\Operation(
	 * 		method="GET",
	 * 		summary="Gets an article category",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="category_id",
	 *				description="ID of the category that needs to be searched.",
	 *				paramType="path",
	 *				required=true,
	 *				type="integer"
	 *			)
	 *		),
	 *		@SWG\ResponseMessage(code=404, message="Article Category not found")
	 * 	)
	 * )
	 */
	public function getCategoryAction($category_id)
	{
		$category = $this->_getCategoryOr404($category_id);

		return $this->createApiResponse(array('category' => $category->toApiData()));
	}

	/**
	 * @SWG\Api(
	 * 	path="/kb/categories/{category_id}",
	 * 	@SWG\Operation(
	 * 		method="POST",
	 * 		summary="Updates an article category.",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="category_id",
	 *				description="ID of the category that needs to be updated.",
	 *				paramType="path",
	 *				required=true,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="title",
	 *				description="Title of the category.",
	 *				paramType="query",
	 *				required=true,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="parent_id",
	 *				description="ID of the category's parent. Use 0 for no parent.",
	 *				paramType="query",
	 *				required=false,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="display_order",
	 *				description="Order of display of categories. Lower numbers will be displayed first.",
	 *				paramType="query",
	 *				required=false,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="usergroup_id[]",
	 *				description="ID of user group that has access. If not provided, defaults to all users.",
	 *				paramType="query",
	 *				required=false,
	 *				type="integer"
	 *			)
	 *		),
	 *		@SWG\ResponseMessage(code=404, message="Article Category not found")
	 * 	)
	 * )
	 */
	public function postCategoryAction($category_id)
	{
		$category = $this->_getCategoryOr404($category_id);

		$errors = array();

		if ($this->in->checkIsset('title')) {
			$title = $this->in->getString('title');
			if (!$title) {
				$errors['title'] = array('required_field.title', 'title empty or missing');
			}
			$category->title = $title;
		}


		if ($this->in->checkIsset('parent_id')) {
			$parent_id = $this->in->getUint('parent_id');
			if ($parent_id) {
				$parent = $this->em->getRepository('DeskPRO:ArticleCategory')->find($parent_id);
				if ($parent) {
					$category->setParent($parent);
				}
			} else {
				$category->setParent(null);
			}
		}

		if ($this->in->checkIsset('display_order')) {
			$category->display_order = $this->in->getUint('display_order');
		}

		$this->em->persist($category);
		$this->em->flush();

		return $this->createSuccessResponse();
	}

	/**
	 * @SWG\Api(
	 * 	path="/kb/categories/{category_id}",
	 * 	@SWG\Operation(
	 * 		method="DELETE",
	 * 		summary="DELETEs an article category",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="category_id",
	 *				description="ID of the category that needs to be deleted.",
	 *				paramType="path",
	 *				required=true,
	 *				type="integer"
	 *			)
	 *		),
	 *		@SWG\ResponseMessage(code=404, message="Article Category not found")
	 * 	)
	 * )
	 */
	public function deleteCategoryAction($category_id)
	{
		$category = $this->_getCategoryOr404($category_id);

		try {
			\Application\DeskPRO\Publish\CategoryEdit::deleteCategory('articles', $category_id);
		} catch (\OutOfBoundsException $e) {
			return $this->createApiErrorResponse('invalid_argument.category_id', 'category is not empty');
		}

		return $this->createSuccessResponse();
	}

	/**
	 * @SWG\Api(
	 * 	path="/kb/categories/{category_id}/articles",
	 * 	@SWG\Operation(
	 * 		method="GET",
	 * 		summary="Gets articles within an article category.",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="category_id",
	 *				description="ID of the category that needs to be searched.",
	 *				paramType="path",
	 *				required=true,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="order",
	 *				description="Order of the results. Defaults to the publishing date.",
	 *				paramType="query",
	 *				required=false,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="cache_id",
	 *				description="If provided, cached results from this result set are used. If it cannot be found or used, the other constraints provided will be used to create a new result set.",
	 *				paramType="query",
	 *				required=false,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="page",
	 *				description="The page number of the results to fetch.",
	 *				paramType="query",
	 *				required=false,
	 *				type="integer"
	 *			)
	 *		),
	 *		@SWG\ResponseMessage(code=404, message="Article Category not found")
	 * 	)
	 * )
	 */
	public function getCategoryArticlesAction($category_id)
	{
		$category = $this->_getCategoryOr404($category_id);

		$terms = array(
			array('type' => ArticleSearch::TERM_CATEGORY_SPECIFIC, 'op' => 'contains', 'options' => array($category->id))
		);

		$order_by = $this->in->getString('order');
		if (!$order_by) {
			$order_by = 'date:desc';
		}

		$extra = array();
		if ($order_by !== null) {
			$extra['order_by'] = $order_by;
		}

		$result_cache = $this->getApiSearchResult('article', $terms, $extra, $this->in->getUint('cache_id'), new ArticleSearch());

		$page = $this->in->getUint('page');
		if (!$page) $page = 1;

		$per_page = Numbers::bound($this->in->getUint('per_page') ?: 25, 1, 250);

		$ids = $result_cache->results;

		$page_ids = \Orb\Util\Arrays::getPageChunk($ids, $page, $per_page);
		$articles = App::getEntityRepository('DeskPRO:Article')->getByIds($page_ids, true);

		return $this->createApiResponse(array(
			'page' => $page,
			'per_page' => $per_page,
			'total' => count($ids),
			'cache_id' => $result_cache->id,
			'articles' => $this->getApiData($articles)
		));
	}

	/**
	 * @SWG\Api(
	 * 	path="/kb/categories/{category_id}/groups",
	 * 	@SWG\Operation(
	 * 		method="GET",
	 * 		summary="Gets groups with access to a article category.",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="category_id",
	 *				description="ID of the category to look for.",
	 *				paramType="path",
	 *				required=true,
	 *				type="integer"
	 *			)
	 *		),
	 *		@SWG\ResponseMessage(code=404, message="Article Category not found")
	 * 	)
	 * )
	 */
	public function getCategoryGroupsAction($category_id)
	{
		$category = $this->_getCategoryOr404($category_id);

		return $this->createApiResponse(array('groups' => $this->getApiData($category->usergroups)));
	}

	/**
	 * @SWG\Api(
	 * 	path="/kb/categories/{category_id}/groups",
	 * 	@SWG\Operation(
	 * 		method="POST",
	 * 		summary="Adds a group to a article category.",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="category_id",
	 *				description="ID of the category to look for.",
	 *				paramType="path",
	 *				required=true,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="id",
	 *				description="ID of the group to add access for.",
	 *				paramType="query",
	 *				required=true,
	 *				type="integer"
	 *			)
	 *		),
	 *		@SWG\ResponseMessage(code=404, message="Article Category not found")
	 * 	)
	 * )
	 */
	public function postCategoryGroupsAction($category_id)
	{
		$category = $this->_getCategoryOr404($category_id);

		$group_id = $this->in->getUint('id');

		$group = $this->em->getRepository('DeskPRO:Usergroup')->find($group_id);
		if (!$group || $group->is_agent_group) {
			return $this->createApiErrorResponse('invalid_argument.id', 'group cannot be found or is not available');
		}

		$exists = false;
		foreach ($category->usergroups AS $group) {
			if ($group->id == $group_id) {
				$exists = true;
				break;
			}
		}

		if (!$exists) {
			$this->db->insert('article_category2usergroup', array(
				'category_id' => $category->id,
				'usergroup_id' => $group_id
			));
		}

		return $this->createApiCreateResponse(
			array('id' => $group_id),
			$this->generateUrl('api_kb_category_group', array('category_id' => $category->id, 'group_id' => $group_id), true)
		);
	}

	/**
	 * @SWG\Api(
	 * 	path="/kb/categories/{category_id}/groups/{group_id}",
	 * 	@SWG\Operation(
	 * 		method="GET",
	 * 		summary="Determines if a group has access to a article category.",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="category_id",
	 *				description="ID of the category to look for.",
	 *				paramType="path",
	 *				required=true,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="group_id",
	 *				description="ID of the group to look for.",
	 *				paramType="path",
	 *				required=true,
	 *				type="integer"
	 *			)
	 *		),
	 *		@SWG\ResponseMessage(code=404, message="Article Category not found")
	 * 	)
	 * )
	 */
	public function getCategoryGroupAction($category_id, $group_id)
	{
		$category = $this->_getCategoryOr404($category_id);

		$exists = false;
		foreach ($category->usergroups AS $group) {
			if ($group->id == $group_id) {
				$exists = true;
				break;
			}
		}

		return $this->createApiResponse(array('exists' => $exists));
	}

	/**
	 * @SWG\Api(
	 * 	path="/kb/categories/{category_id}/groups/{group_id}",
	 * 	@SWG\Operation(
	 * 		method="DELETE",
	 * 		summary="Removes a group's access to a article category.",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="category_id",
	 *				description="ID of the category to look for.",
	 *				paramType="path",
	 *				required=true,
	 *				type="integer"
	 *			),
	 *			@SWG\Parameter(
	 *				name="group_id",
	 *				description="ID of the group to look for.",
	 *				paramType="path",
	 *				required=true,
	 *				type="integer"
	 *			)
	 *		),
	 *		@SWG\ResponseMessage(code=404, message="Article Category not found")
	 * 	)
	 * )
	 */
	public function deleteCategoryGroupAction($category_id, $group_id)
	{
		$category = $this->_getCategoryOr404($category_id);

		foreach ($category->usergroups AS $key => $group) {
			if ($group->id == $group_id) {
				$category->usergroups->remove($key);
				$this->em->persist($category);
				$this->em->flush();
				break;
			}
		}

		return $this->createSuccessResponse();
	}

	/**
	 * @SWG\Api(
	 * 	path="/kb/fields",
	 * 	@SWG\Operation(
	 * 		method="GET",
	 * 		summary="Gets available article fields."
	 * 	)
	 * )
	 */
	public function getFieldsAction()
	{
		$field_manager = $this->container->getSystemService('article_fields_manager');
		$fields = $field_manager->getFields();

		return $this->createApiResponse(array('fields' => $this->getApiData($fields)));
	}

	/**
	 * @SWG\Api(
	 * 	path="/kb/products",
	 * 	@SWG\Operation(
	 * 		method="GET",
	 * 		summary="Gets available article products."
	 * 	)
	 * )
	 */
	public function getProductsAction()
	{
		$products = $this->em->getRepository('DeskPRO:Product')->getFlatHierarchy();

		return $this->createApiResponse(array('products' => $products));
	}

	/**
	 * @param integer $id
	 * @return \Application\DeskPRO\Entity\Article
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	protected function _getArticleOr404($id, $check_perm = false)
	{
		$article = $this->em->getRepository('DeskPRO:Article')->findOneById($id);

		if (!$article) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no article with ID $id");
		}

		if ($check_perm) {
			if ($check_perm == 'edit' && !$this->person->PermissionsManager->PublishChecker->canEdit($article)) {
				throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
			}

			if ($check_perm == 'delete' && !$this->person->PermissionsManager->PublishChecker->canDelete($article)) {
				throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
			}
		}

		return $article;
	}

	/**
	 * @param integer $id
	 * @return \Application\DeskPRO\Entity\ArticleCategory
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	protected function _getCategoryOr404($id)
	{
		$category = $this->em->getRepository('DeskPRO:ArticleCategory')->find($id);

		if (!$category) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no category with ID $id");
		}

		return $category;
	}
}

<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\ContentRevision\Util as ContentRevisionUtil;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsComment;
use Application\DeskPRO\Searcher\NewsSearch;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Orb\Util\Numbers;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * SWG\Resource(
 * 	resourcePath="/news",
 * 	description="Operations about News Items",
 * 	basePath="/api"
 * ).
 *
 * @ApiModes("all")
 */
class NewsController extends AbstractController
{
    /**
     * SWG\Api(
     * 	path="/news",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Get list of all News Items",
     * 		notes="Returns array of all existing News Items",
     *		type="array",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="category_id",
     *				description="Category ID that needs to be searched",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			),
     *			SWG\Parameter(
     *				name="category_id_specific",
     *				description="Specific Category ID that needs to be searched",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			),
     *			SWG\Parameter(
     *				name="label",
     *				description="News label that needs to be searched",
     *				paramType="query",
     *				required=false,
     *				type="string"
     *			),
     *			SWG\Parameter(
     *				name="status",
     *				description="News status that needs to be searched",
     *				paramType="query",
     *				required=false,
     *				type="string"
     *			)
     *		)
     * 	)
     * ).
     */
    public function searchAction()
    {
        $search_map = [
            'category_id'          => NewsSearch::TERM_CATEGORY,
            'category_id_specific' => NewsSearch::TERM_CATEGORY_SPECIFIC,
            'label'                => NewsSearch::TERM_LABEL,
            'status'               => NewsSearch::TERM_STATUS,
        ];

        $terms = [];

        foreach ($search_map as $input => $search_key) {
            $value = $this->in->getCleanValueArray($input, 'raw', 'discard');
            if ($value) {
                $terms[] = ['type' => $search_key, 'op' => 'contains', 'options' => $value];
            }
        }

        $date_created_start = $this->in->getUint('date_created_start');
        $date_created_end   = $this->in->getUint('date_created_end');
        if ($date_created_end) {
            $terms[] = ['type' => NewsSearch::TERM_DATE_CREATED, 'op' => 'between', 'options' => [
                'date1' => $date_created_start,
                'date2' => $date_created_end,
            ]];
        } elseif ($date_created_start) {
            $terms[] = ['type' => NewsSearch::TERM_DATE_CREATED, 'op' => 'between', 'options' => [
                'date1' => $date_created_start,
            ]];
        }

        $order_by = $this->in->getString('order');
        if (!$order_by) {
            $order_by = 'date:desc';
        }

        $extra = [];
        if ($order_by !== null) {
            $extra['order_by'] = $order_by;
        }

        $result_cache = $this->getApiSearchResult('news', $terms, $extra, $this->in->getUint('cache_id'), new NewsSearch());

        $page = $this->in->getUint('page');
        if (!$page) {
            $page = 1;
        }

        $per_page = Numbers::bound($this->in->getUint('per_page') ?: 25, 1, 250);

        $ids = $result_cache->results;

        $page_ids = \Orb\Util\Arrays::getPageChunk($ids, $page, $per_page);
        $news     = App::getEntityRepository('DeskPRO:News')->getByIds($page_ids, true);

        return $this->createApiResponse([
            'page'     => $page,
            'per_page' => $per_page,
            'total'    => count($ids),
            'cache_id' => $result_cache->id,
            'news'     => $this->getApiData($news),
        ]);
    }

    /**
     * SWG\Api(
     * 	path="/news",
     * 	SWG\Operation(
     * 		method="POST",
     * 		summary="Add a new News Item",
     * 		notes="Creates a new News Item and returns the ID",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="body",
     *				description="News Object to Add",
     *				paramType="body",
     *				required=true,
     *				type="News"
     *			)
     *		)
     * 	)
     * ).
     */
    public function newNewsAction()
    {
        $errors = [];
        $news   = new News();

        $title = $this->in->getString('title');
        if ($title) {
            $news->title = $title;
        } else {
            $errors['title'] = ['required_field.title', 'title is required'];
        }

        $content = $this->in->getHtml('content');
        if ($content) {
            $news->content = $content;
        } else {
            $errors['content'] = ['required_field.content', 'content is required'];
        }

        $status = $this->in->getString('status');
        if (!$status) {
            $status = 'published';
        }
        $news->setStatusCode($status);

        $date = $this->in->getUint('date');
        if ($date) {
            $news->date_created = new \DateTime('@'.$date);
            if ($status == 'published') {
                $news->date_published = new \DateTime('@'.$date);
            }
        }

        $cat = $this->em->find('DeskPRO:NewsCategory', $this->in->getUint('category_id'));
        if (!$cat) {
            $errors['category_id'] = ['invalid_argument.category_id', 'category_id not found'];
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
            ['id' => $news->id],
            $this->generateUrl(
                'api_news_news',
                ['news_id' => $news->id],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * SWG\Api(
     * 	path="/news/{news_id}",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Find News by ID",
     * 		notes="Returns a News Item based on ID",
     *		type="News",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="news_id",
     *				description="ID of the news item that needs to be fetched",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="News Item not found")
     * 	)
     * ).
     */
    public function getNewsAction($news_id)
    {
        $news = $this->_getNewsOr404($news_id);

        return $this->createApiResponse(['news' => $news->toApiData()]);
    }

    /**
     * SWG\Api(
     * 	path="/news/{news_id}",
     * 	SWG\Operation(
     * 		method="POST",
     * 		summary="Updates News Item by ID",
     * 		notes="Updated a News Item with form data",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="news_id",
     *				description="ID of the news item that needs to be updated",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			SWG\Parameter(
     *				name="title",
     *				description="Updated title of the News Item",
     *				paramType="form",
     *				required=false,
     *				type="string"
     *			),
     *			SWG\Parameter(
     *				name="status",
     *				description="Updated status of the News Item",
     *				paramType="form",
     *				required=false,
     *				type="string"
     *			),
     *			SWG\Parameter(
     *				name="date_published",
     *				description="Updated published date of the News Item",
     *				paramType="form",
     *				required=false,
     *				type="string"
     *			),
     *			SWG\Parameter(
     *				name="content",
     *				description="Updated content of the News Item",
     *				paramType="form",
     *				required=false,
     *				type="string"
     *			),
     *			SWG\Parameter(
     *				name="category_id",
     *				description="Updated category_id of the News Item",
     *				paramType="form",
     *				required=false,
     *				type="integer"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="News Item not found")
     * 	)
     * ).
     */
    public function postNewsAction($news_id)
    {
        $news = $this->_getNewsOr404($news_id, 'edit');

        $revs = [];

        $title = $this->in->getString('title');
        if ($title) {
            $news->title = $title;

            $rev        = ContentRevisionUtil::findOrCreate($news, 'title', $this->person);
            $rev->title = $news->title;

            $revs['title'] = $rev;
        }

        $status = $this->in->getString('status');
        if ($status) {
            $news->status = $status;
        }

        $date = $this->in->getUint('date_published');
        if ($date && $news->status == 'published') {
            $news->date_published = new \DateTime('@'.$date);
        }

        $content = $this->in->getString('content');
        if ($content && $content != $news->content) {
            $news->content = $this->in->getHtml('content');

            $rev          = ContentRevisionUtil::findOrCreate($news, ['content'], $this->person);
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

        foreach ($revs as $rev) {
            $this->em->persist($rev);
        }
        $this->em->persist($news);
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * SWG\Api(
     * 	path="/news/{news_id}",
     * 	SWG\Operation(
     * 		method="DELETE",
     * 		summary="Delete News by ID",
     * 		notes="Deletes a News Item based on ID",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="news_id",
     *				description="ID of the news item that needs to be deleted",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="News Item not found")
     * 	)
     * ).
     */
    public function deleteNewsAction($news_id)
    {
        $news = $this->_getNewsOr404($news_id, 'delete');

        $news->status_code = 'hidden.deleted';
        $this->em->persist($news);
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * SWG\Api(
     * 	path="/news/{news_id}/comments",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Find all the comments by News ID",
     * 		notes="Retrieves all news comments based on News ID",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="news_id",
     *				description="ID of the news item that needs to be searched for comments",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="News Item not found")
     * 	)
     * ).
     */
    public function getNewsCommentsAction($news_id)
    {
        $news     = $this->_getNewsOr404($news_id);
        $comments = $this->em->getRepository('DeskPRO:NewsComment')->getComments($news);

        return $this->createApiResponse(['comments' => $this->getApiData($comments)]);
    }

    /**
     * SWG\Api(
     * 	path="/news/{news_id}/comments",
     * 	SWG\Operation(
     * 		method="POST",
     * 		summary="Adds a new Comment by News ID",
     * 		notes="Creates a news comments based on News ID",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="news_id",
     *				description="ID of the news item that needs to be searched for comments",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="content",
     *				description="Comment content",
     *				paramType="query",
     *				required=true,
     *				type="string"
     *			)
     *		),
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="person_id",
     *				description="ID of the person making the comment",
     *				paramType="query",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="status",
     *				description="status of the comment",
     *				paramType="query",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="News Item not found")
     * 	)
     * ).
     */
    public function newNewsCommentAction($news_id)
    {
        $news = $this->_getNewsOr404($news_id);

        $content = $this->in->getString('content');
        if (!$content) {
            return $this->createApiErrorResponse('required_field.content', 'Missing content');
        }

        $person_id = $this->in->getUint('person_id');
        $person    = null;
        if ($person_id) {
            $person = $this->em->getRepository('DeskPRO:Person')->find($person_id);
        }

        $status = $this->in->getString('status');

        $comment                 = new NewsComment();
        $comment->news           = $news;
        $comment->person         = $person ?: $this->person;
        $comment['content']      = $content;
        $comment['status']       = $status ?: 'visible';
        $comment['is_reviewed']  = ($comment['status'] == 'visible' && !$person);
        $comment['date_created'] = new \DateTime();

        $this->em->persist($comment);
        $this->em->flush();

        return $this->createApiCreateResponse(
            ['id' => $comment->id],
            $this->generateUrl(
                'api_news_news_comments_comment',
                ['news_id' => $news->id, 'comment_id' => $comment->id],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * SWG\Api(
     * 	path="/news/{news_id}/comments/{comment_id}",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Find one News comment by News ID and Comment ID",
     * 		notes="Retrieves a news comments based on News ID and Comment ID",
     *		type="NewsComment",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="news_id",
     *				description="ID of the news item that needs to be searched for comments",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			SWG\Parameter(
     *				name="comment_id",
     *				description="ID of the comment that needs to be searched for comments",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="News Item not found")
     * 	)
     * ).
     */
    public function getNewsCommentAction($news_id, $comment_id)
    {
        $news    = $this->_getNewsOr404($news_id);
        $comment = $this->em->getRepository('DeskPRO:NewsComment')->find($comment_id);
        if (!$comment || $comment->news->id != $news->id) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        return $this->createApiResponse(['comment' => $comment->toApiData()]);
    }

    /**
     * SWG\Api(
     * 	path="/news/{news_id}/comments/{comment_id}",
     * 	SWG\Operation(
     * 		method="POST",
     * 		summary="Updates one News comment by News ID and Comment ID",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="news_id",
     *				description="ID of the news item that needs to be searched for comments",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			SWG\Parameter(
     *				name="comment_id",
     *				description="ID of the comment that needs to be searched for comments",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			SWG\Parameter(
     *				name="status",
     *				description="status of the comment",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			),
     *			SWG\Parameter(
     *				name="content",
     *				description="Comment content",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="News Item not found")
     * 	)
     * ).
     */
    public function postNewsCommentAction($news_id, $comment_id)
    {
        $news    = $this->_getNewsOr404($news_id);
        $comment = $this->em->getRepository('DeskPRO:NewsComment')->find($comment_id);
        if (!$comment || $comment->news->id != $news->id) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        $approved = false;
        $status   = $this->in->getString('status');
        if ($status) {
            $approved        = ($status == 'visible' && $comment->status != 'visible');
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
     * SWG\Api(
     * 	path="/news/{news_id}/comments/{comment_id}",
     * 	SWG\Operation(
     * 		method="DELETE",
     * 		summary="Delete one News comment by News ID and Comment ID",
     * 		notes="Deletes a news comments based on News ID and Comment ID",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="news_id",
     *				description="ID of the news item that needs to be searched for comments",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			SWG\Parameter(
     *				name="comment_id",
     *				description="ID of the comment that needs to be searched for comments",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="News Item not found")
     * 	)
     * ).
     */
    public function deleteNewsCommentAction($news_id, $comment_id)
    {
        $news    = $this->_getNewsOr404($news_id);
        $comment = $this->em->getRepository('DeskPRO:NewsComment')->find($comment_id);
        if (!$comment || $comment->news->id != $news->id) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        $this->em->remove($comment);
        $this->em->flush();

        $this->_sendCommentDeletedNotification($comment);

        return $this->createSuccessResponse();
    }

    /**
     * SWG\Api(
     * 	path="/news/{news_id}/labels",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Find all the labels by News ID",
     * 		notes="Retrieves all labels based on News ID",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="news_id",
     *				description="ID of the news item that needs to be searched for labels",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="News Item not found")
     * 	)
     * ).
     */
    public function getNewsLabelsAction($news_id)
    {
        $news = $this->_getNewsOr404($news_id);

        return $this->createApiResponse(['labels' => $this->getApiData($news->labels)]);
    }

    /**
     * SWG\Api(
     * 	path="/news/{news_id}/labels",
     * 	SWG\Operation(
     * 		method="POST",
     * 		summary="Adds a new Label by News ID",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="news_id",
     *				description="ID of the news item that needs to be searched for comments",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="label",
     *				description="Label",
     *				paramType="query",
     *				required=true,
     *				type="string"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="News Item not found")
     * 	)
     * ).
     */
    public function postNewsLabelsAction($news_id)
    {
        $news  = $this->_getNewsOr404($news_id, 'edit');
        $label = $this->in->getString('label');

        if ($label === '') {
            return $this->createApiErrorResponse('required_field', "Field 'label' missing or empty");
        }

        $news->getLabelManager()->addLabel($label);
        $this->em->persist($news);
        $this->em->flush();

        return $this->createApiCreateResponse(
            ['label' => $label],
            $this->generateUrl(
                'api_news_news_label',
                ['news_id' => $news->id, 'label' => $label],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * SWG\Api(
     * 	path="/news/{news_id}/labels/{label}",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Find one Label by News ID and Label",
     * 		notes="Retrieves a label based on News ID and Label",
     *		type="NewsComment",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="news_id",
     *				description="ID of the news item that needs to be searched for comments",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			SWG\Parameter(
     *				name="label",
     *				description="label that needs to be searched",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="News Item not found")
     * 	)
     * ).
     */
    public function getNewsLabelAction($news_id, $label)
    {
        $news = $this->_getNewsOr404($news_id);

        if ($news->getLabelManager()->hasLabel($label)) {
            return $this->createApiResponse(['exists' => true]);
        } else {
            return $this->createApiResponse(['exists' => false]);
        }
    }

    /**
     * SWG\Api(
     * 	path="/news/{news_id}/comments/{label}",
     * 	SWG\Operation(
     * 		method="DELETE",
     * 		summary="Delete one label by News ID and label",
     * 		notes="Deletes a news comments based on News ID and Comment ID",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="news_id",
     *				description="ID of the news item that needs to be searched for comments",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			SWG\Parameter(
     *				name="comment_id",
     *				description="ID of the comment that needs to be searched for comments",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="News Item not found")
     * 	)
     * ).
     */
    public function deleteNewsLabelAction($news_id, $label)
    {
        $news = $this->_getNewsOr404($news_id, 'edit');

        $news->getLabelManager()->removeLabel($label);
        $this->em->persist($news);
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * SWG\Api(
     * 	path="/news/validating-comments",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Gets news comments that are awaiting validation"
     * 	)
     * ).
     */
    public function getValidatingCommentsAction()
    {
        $comments   = $this->em->getRepository('DeskPRO:NewsComment')->getValidatingComments();
        $entity_key = 'news';
        $output     = [];
        foreach ($comments as $key => $value) {
            $output[$key] = $value->toApiData(false, true);
            if ($value->$entity_key) {
                $output[$key][$entity_key] = $value->$entity_key->toApiData(false, false);
            }
        }

        return $this->createApiResponse(['comments' => $output]);
    }

    /**
     * SWG\Api(
     * 	path="/news/categories",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Gets available news categories",
     * 		notes="Retrieves all available news categories"
     * 	)
     * ).
     */
    public function getCategoriesAction()
    {
        $categories = $this->em->getRepository('DeskPRO:NewsCategory')->getFlatHierarchy();

        return $this->createApiResponse(['categories' => $categories]);
    }

    /**
     * SWG\Api(
     * 	path="/news/categories",
     * 	SWG\Operation(
     * 		method="POST",
     * 		summary="Creates a new News category"
     * 	)
     * ).
     */
    public function postCategoriesAction()
    {
        $errors = [];

        $title = $this->in->getString('title');
        if (!$title) {
            $errors['title'] = ['required_field.title', 'title empty or missing'];
        }

        $category = new \Application\DeskPRO\Entity\NewsCategory();

        $category->title = $title;

        $parent_id = $this->in->getUint('parent_id');
        if ($parent_id) {
            $parent = $this->em->getRepository('DeskPRO:NewsCategory')->find($parent_id);
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
            $usergroup_ids = [1];
        }

        $this->db->beginTransaction();

        try {
            $this->em->persist($category);
            $this->em->flush();

            foreach ($usergroup_ids as $usergroup_id) {
                if (!$usergroup_id) {
                    continue;
                }
                App::getDb()->insert('news_category2usergroup', [
                    'category_id'  => $category->getId(),
                    'usergroup_id' => $usergroup_id,
                ]);
            }

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        return $this->createApiCreateResponse(
            ['id' => $category->id],
            $this->generateUrl(
                'api_news_category',
                ['category_id' => $category->id],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * SWG\Api(
     * 	path="/news/categories/{catgory_id}",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Find News Category by ID",
     * 		notes="Returns a News Category based on ID",
     *		type="NewsCategory",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="category_id",
     *				description="ID of the news category that needs to be fetched",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="News Category not found")
     * 	)
     * ).
     */
    public function getCategoryAction($category_id)
    {
        $category = $this->_getCategoryOr404($category_id);

        return $this->createApiResponse(['category' => $category->toApiData()]);
    }

    /**
     * SWG\Api(
     * 	path="/news/categories/{catgory_id}",
     * 	SWG\Operation(
     * 		method="POST",
     * 		summary="Update News Category by ID",
     * 		notes="Updates a News Category based on ID",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="category_id",
     *				description="ID of the news category that needs to be updated",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			SWG\Parameter(
     *				name="title",
     *				description="Updated category title",
     *				paramType="path",
     *				required=false,
     *				type="string"
     *			),
     *			SWG\Parameter(
     *				name="parent_id",
     *				description="Updated parent ID",
     *				paramType="path",
     *				required=false,
     *				type="integer"
     *			),
     *			SWG\Parameter(
     *				name="display_order",
     *				description="Updated display order",
     *				paramType="path",
     *				required=false,
     *				type="string"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="News Category not found")
     * 	)
     * ).
     */
    public function postCategoryAction($category_id)
    {
        $category = $this->_getCategoryOr404($category_id);

        $errors = [];

        if ($this->in->checkIsset('title')) {
            $title = $this->in->getString('title');
            if (!$title) {
                $errors['title'] = ['required_field.title', 'title empty or missing'];
            }
            $category->title = $title;
        }

        if ($this->in->checkIsset('parent_id')) {
            $parent_id = $this->in->getUint('parent_id');
            if ($parent_id) {
                $parent = $this->em->getRepository('DeskPRO:NewsCategory')->find($parent_id);
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
     * SWG\Api(
     * 	path="/news/categories/{category_id}",
     * 	SWG\Operation(
     * 		method="DELETE",
     * 		summary="Delete News Category by ID",
     * 		notes="Deletes a News Category based on ID",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="category_id",
     *				description="ID of the news category that needs to be deleted",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="News Category not found")
     * 	)
     * ).
     */
    public function deleteCategoryAction($category_id)
    {
        try {
            \Application\DeskPRO\Publish\CategoryEdit::deleteCategory('news', $category_id);
        } catch (\OutOfBoundsException $e) {
            return $this->createApiErrorResponse('invalid_argument.category_id', 'category is not empty');
        }

        return $this->createSuccessResponse();
    }

    /**
     * SWG\Api(
     * 	path="/news/categories/{catgory_id}",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Gets news within a news category",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="category_id",
     *				description="ID of the news category that needs to be searched",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="News Category not found")
     * 	)
     * ).
     */
    public function getCategoryNewsAction($category_id)
    {
        $category = $this->_getCategoryOr404($category_id);

        $terms = [
            ['type' => NewsSearch::TERM_CATEGORY_SPECIFIC, 'op' => 'contains', 'options' => [$category->id]],
        ];

        $order_by = $this->in->getString('order');
        if (!$order_by) {
            $order_by = 'date:desc';
        }

        $extra = [];
        if ($order_by !== null) {
            $extra['order_by'] = $order_by;
        }

        $result_cache = $this->getApiSearchResult('news', $terms, $extra, $this->in->getUint('cache_id'), new NewsSearch());

        $page = $this->in->getUint('page');
        if (!$page) {
            $page = 1;
        }

        $per_page = Numbers::bound($this->in->getUint('per_page') ?: 25, 1, 250);

        $ids = $result_cache->results;

        $page_ids = \Orb\Util\Arrays::getPageChunk($ids, $page, $per_page);
        $news     = App::getEntityRepository('DeskPRO:News')->getByIds($page_ids, true);

        return $this->createApiResponse([
            'page'     => $page,
            'per_page' => $per_page,
            'total'    => count($ids),
            'cache_id' => $result_cache->id,
            'news'     => $this->getApiData($news),
        ]);
    }

    /**
     * SWG\Api(
     * 	path="/news/categories/{category_id}/groups",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Gets groups with access to a news category",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="category_id",
     *				description="ID of the news category that needs to be searched",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="News Category not found")
     * 	)
     * ).
     */
    public function getCategoryGroupsAction($category_id)
    {
        $category = $this->_getCategoryOr404($category_id);

        return $this->createApiResponse(['groups' => $this->getApiData($category->usergroups)]);
    }

    /**
     * SWG\Api(
     * 	path="/news/categories/{category_id}/groups",
     * 	SWG\Operation(
     * 		method="POST",
     * 		summary="Adds a group to a news category",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="category_id",
     *				description="ID of the news category where the new group needs to be added",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			SWG\Parameter(
     *				name="id",
     *				description="ID of the group to add access for.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="News Category not found")
     * 	)
     * ).
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
        foreach ($category->usergroups as $group) {
            if ($group->id == $group_id) {
                $exists = true;
                break;
            }
        }

        if (!$exists) {
            $this->db->insert('news_category2usergroup', [
                'category_id'  => $category->id,
                'usergroup_id' => $group_id,
            ]);
        }

        return $this->createApiCreateResponse(
            ['id' => $group_id],
            $this->generateUrl(
                'api_news_category_group',
                ['category_id' => $category->id, 'group_id' => $group_id],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * SWG\Api(
     * 	path="/news/categories/{category_id}/groups/{group_id}",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Determines if a group has access to a news category",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="category_id",
     *				description="ID of the news category that needs to be checked",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			SWG\Parameter(
     *				name="group_id",
     *				description="ID of the group that needs to be checked",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="News Category not found")
     * 	)
     * ).
     */
    public function getCategoryGroupAction($category_id, $group_id)
    {
        $category = $this->_getCategoryOr404($category_id);

        $exists = false;
        foreach ($category->usergroups as $group) {
            if ($group->id == $group_id) {
                $exists = true;
                break;
            }
        }

        return $this->createApiResponse(['exists' => $exists]);
    }

    /**
     * SWG\Api(
     * 	path="/news/categories/{category_id}/groups/{group_id}",
     * 	SWG\Operation(
     * 		method="DELETE",
     * 		summary="Removes a group's access to a news category",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="category_id",
     *				description="ID of the news category that needs to be checked",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			SWG\Parameter(
     *				name="group_id",
     *				description="ID of the group that needs to be checked",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="News Category not found")
     * 	)
     * ).
     */
    public function deleteCategoryGroupAction($category_id, $group_id)
    {
        $category = $this->_getCategoryOr404($category_id);

        foreach ($category->usergroups as $key => $group) {
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
     * @param int $id
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return \Application\DeskPRO\Entity\News
     */
    protected function _getNewsOr404($id, $check_perm = false)
    {
        $news = $this->em->getRepository('DeskPRO:News')->findOneById($id);

        if (!$news) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no news with ID $id");
        }

        if ($check_perm) {
            if ($check_perm == 'edit' && !$this->person->PermissionsManager->PublishChecker->canEdit($news)) {
                throw new AccessDeniedHttpException('Sorry, you do not have permission to perform this action');
            }

            if ($check_perm == 'delete' && !$this->person->PermissionsManager->PublishChecker->canDelete($news)) {
                throw new AccessDeniedHttpException('Sorry, you do not have permission to perform this action');
            }
        }

        return $news;
    }

    /**
     * @param int $id
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return \Application\DeskPRO\Entity\NewsCategory
     */
    protected function _getCategoryOr404($id)
    {
        $category = $this->em->getRepository('DeskPRO:NewsCategory')->find($id);

        if (!$category) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no category with ID $id");
        }

        return $category;
    }
}

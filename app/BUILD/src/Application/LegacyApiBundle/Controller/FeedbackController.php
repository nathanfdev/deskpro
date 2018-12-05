<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\ContentRevision\Util as ContentRevisionUtil;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Searcher\FeedbackSearch;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Orb\Util\Numbers;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * SWG\Resource(
 * 	resourcePath="/feedback",
 * 	description="Operations about Feedbacks",
 * 	basePath="/api"
 * ).
 *
 * @ApiModes("all")
 */
class FeedbackController extends AbstractController
{
    /**
     * SWG\Api(
     * 	path="/feedback",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Search for feedbacks matching criteria",
     * 		notes="Returns list of feedbacks that matched.",
     *		type="array",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="category_id[]",
     *				description="Comma seperated IDs of categories to search in",
     *				paramType="query",
     *				required=false,
     *				type="string"
     *			),
     *			SWG\Parameter(
     *				name="category_id_specific[]",
     *				description="Comma seperated IDs of categories to search in",
     *				paramType="query",
     *				required=false,
     *				type="string"
     *			),
     *			SWG\Parameter(
     *				name="date_created_end",
     *				description="Requires the feedback to have been created before this date. Must be specified as a Unix timestamp.",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			),
     *			SWG\Parameter(
     *				name="date_created_start",
     *				description="Requires the feedback to have been created after this date. Must be specified as a Unix timestamp.",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			),
     *			SWG\Parameter(
     *				name="label[]",
     *				description="Requires the feedback to have this label.",
     *				paramType="query",
     *				required=false,
     *				type="string"
     *			),
     *			SWG\Parameter(
     *				name="status[]",
     *				description="Requires the feedback to be in this status. Possible values: new, active, closed, hidden.",
     *				paramType="query",
     *				required=false,
     *				type="string"
     *			),
     *			SWG\Parameter(
     *				name="status_category_id[]",
     *				description="Requires the feedback to be in this status category.",
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
            'category_id'          => FeedbackSearch::TERM_CATEGORY,
            'category_id_specific' => FeedbackSearch::TERM_CATEGORY_SPECIFIC,
            'label'                => FeedbackSearch::TERM_LABEL,
            'status'               => FeedbackSearch::TERM_STATUS,
            'status_category_id'   => FeedbackSearch::TERM_STATUS_CATEGORY,
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
            $terms[] = ['type' => FeedbackSearch::TERM_DATE_CREATED, 'op' => 'between', 'options' => [
                'date1' => $date_created_start,
                'date2' => $date_created_end,
            ]];
        } elseif ($date_created_start) {
            $terms[] = ['type' => FeedbackSearch::TERM_DATE_CREATED, 'op' => 'between', 'options' => [
                'date1' => $date_created_start,
            ]];
        }

        $order_by = $this->in->getString('order');
        if (!$order_by) {
            $order_by = 'date_created:desc';
        }

        $extra = [];
        if ($order_by !== null) {
            $extra['order_by'] = $order_by;
        }

        $result_cache = $this->getApiSearchResult('feedback', $terms, $extra, $this->in->getUint('cache_id'), new FeedbackSearch());

        $page = $this->in->getUint('page');
        if (!$page) {
            $page = 1;
        }

        $per_page = Numbers::bound($this->in->getUint('per_page') ?: 25, 1, 250);

        $ids = $result_cache->results;

        $page_ids = \Orb\Util\Arrays::getPageChunk($ids, $page, $per_page);
        $feedback = App::getEntityRepository('DeskPRO:Feedback')->getByIds($page_ids, true);

        return $this->createApiResponse([
            'page'     => $page,
            'per_page' => $per_page,
            'total'    => count($ids),
            'cache_id' => $result_cache->id,
            'feedback' => $this->getApiData($feedback),
        ]);
    }

    /**
     * SWG\Api(
     * 	path="/feedbacks",
     * 	SWG\Operation(
     * 		method="POST",
     * 		summary="Creates a new feedback.",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="title",
     *				description="Title of the feedback. ",
     *				paramType="query",
     *				required=true,
     *				type="string"
     *			),
     *			SWG\Parameter(
     *				name="content",
     *				description="Content of the feedback. Marked up using HTML.",
     *				paramType="query",
     *				required=true,
     *				type="string"
     *			),
     *			SWG\Parameter(
     *				name="category_id",
     *				description="Category of the feedback.",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			),
     *			SWG\Parameter(
     *				name="label[]",
     *				description="Comma seperated list of Labels to apply to the feedback.",
     *				paramType="query",
     *				required=false,
     *				type="string"
     *			),
     *			SWG\Parameter(
     *				name="status",
     *				description="Status of the feedback. Defaults to new if not overridden by this or status_category_id.",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			),
     *			SWG\Parameter(
     *				name="status_category_id",
     *				description="Status category of the feedback.",
     *				paramType="query",
     *				required=false,
     *				type="string"
     *			),
     *			SWG\Parameter(
     *				name="user_category_id",
     *				description="User category of the feedback.",
     *				paramType="query",
     *				required=false,
     *				type="string"
     *			)
     *		)
     * 	)
     * ).
     */
    public function newFeedbackAction()
    {
        $errors   = [];
        $feedback = new Feedback();

        $title = $this->in->getString('title');
        if ($title) {
            $feedback->title = $title;
        } else {
            $errors['title'] = ['required_field.title', 'title is required'];
        }

        $content = $this->in->getHtml('content');
        if ($content) {
            $feedback->content = $content;
        } else {
            $errors['content'] = ['required_field.content', 'content is required'];
        }

        $status_cat = $this->em->find('DeskPRO:FeedbackStatusCategory', $this->in->getUint('status_category_id'));
        if ($status_cat) {
            $feedback->setStatusCode($status_cat->status_type.'.'.$status_cat->id);
        } else {
            $status = $this->in->getString('status');
            if (!$status) {
                $status = 'new';
            }
            $feedback->setStatusCode($status);
        }

        $cat = $this->em->find('DeskPRO:FeedbackCategory', $this->in->getUint('category_id'));
        if ($cat) {
            $feedback->category = $cat;
        }

        if ($errors) {
            return $this->createApiMultipleErrorResponse($errors);
        }

        $feedback->person = $this->person;

        $this->_insertFeedbackAttachments($feedback);

        $this->em->persist($feedback);
        $this->em->flush();

        $labels = $this->in->getCleanValueArray('label', 'string', 'discard');
        if ($labels) {
            $feedback->getLabelManager()->setLabelsArray($labels, $this->em);
            $this->em->flush();
        }

        $user_category_id = $this->in->getUint('user_category_id');
        if ($user_category_id) {
            $field         = $this->_getUserCategoryField();
            $field_manager = $this->container->getSystemService('feedback_fields_manager');
            $field_manager->saveFormToObject(['field_'.$field->id => $user_category_id], $feedback, true);
        }

        return $this->createApiCreateResponse(
            ['id' => $feedback->id],
            $this->generateUrl(
                'api_feedback_feedback',
                ['feedback_id' => $feedback->id],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    //Gets information about specific feedback
    /**
     * SWG\Api(
     * 	path="/feedback/{feedback_id}",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Gets a feedback by feedback ID.",
     * 		notes="Information about the feedback by feedback ID.",
     *		type="Feedback",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="feedback_id",
     *				description="ID of the feedback that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="Feedback not found")
     * 	)
     * ).
     */
    public function getFeedbackAction($feedback_id)
    {
        $feedback = $this->_getFeedbackOr404($feedback_id);

        return $this->createApiResponse(['feedback' => $feedback->toApiData()]);
    }

    /**
     * SWG\Api(
     * 	path="/feedbacks/{feedback_id}",
     * 	SWG\Operation(
     * 		method="POST",
     * 		summary="Updates a Feedback by feedback ID.",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="feedback_id",
     *				description="ID of the feedback the needs to be updated.",
     *				paramType="query",
     *				required=true,
     *				type="string"
     *			),
     *			SWG\Parameter(
     *				name="title",
     *				description="Title of the feedback. ",
     *				paramType="query",
     *				required=true,
     *				type="string"
     *			),
     *			SWG\Parameter(
     *				name="content",
     *				description="Content of the feedback. Marked up using HTML.",
     *				paramType="query",
     *				required=true,
     *				type="string"
     *			),
     *			SWG\Parameter(
     *				name="category_id",
     *				description="Category of the feedback.",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			),
     *			SWG\Parameter(
     *				name="label[]",
     *				description="Comma seperated list of Labels to apply to the feedback.",
     *				paramType="query",
     *				required=false,
     *				type="string"
     *			),
     *			SWG\Parameter(
     *				name="status",
     *				description="Status of the feedback. Defaults to new if not overridden by this or status_category_id.",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			),
     *			SWG\Parameter(
     *				name="status_category_id",
     *				description="Status category of the feedback.",
     *				paramType="query",
     *				required=false,
     *				type="string"
     *			),
     *			SWG\Parameter(
     *				name="user_category_id",
     *				description="User category of the feedback.",
     *				paramType="query",
     *				required=false,
     *				type="string"
     *			)
     *		)
     * 	)
     * ).
     */
    public function postFeedbackAction($feedback_id)
    {
        $feedback = $this->_getFeedbackOr404($feedback_id, 'edit');

        $revs = [];

        $title = $this->in->getString('title');
        if ($title) {
            $feedback->title = $title;

            $rev        = ContentRevisionUtil::findOrCreate($feedback, 'title', $this->person);
            $rev->title = $feedback->title;

            $revs['title'] = $rev;
        }

        $content = $this->in->getString('content');
        if ($content && $content != $feedback->content) {
            $feedback->content = $this->in->getHtml('content');

            $rev          = ContentRevisionUtil::findOrCreate($feedback, ['content'], $this->person);
            $rev->content = $feedback->content;

            $revs['content'] = $rev;
        }

        $category_id = $this->in->getUint('category_id');
        if ($category_id) {
            $cat = $this->em->find('DeskPRO:FeedbackCategory', $category_id);
            if ($cat) {
                $feedback->category = $cat;
            }
        }

        $status_category_id = $this->in->getUint('status_category_id');
        if ($status_category_id) {
            $status_cat = $this->em->find('DeskPRO:FeedbackStatusCategory', $this->in->getUint('status_category_id'));
            if ($status_cat) {
                $feedback->setStatusCode($status_cat->status_type.'.'.$status_cat->id);
            }
        } else {
            $status = $this->in->getString('status');
            if ($status) {
                $feedback->setStatusCode($status);
            }
        }

        $this->_insertFeedbackAttachments($feedback);

        foreach ($revs as $rev) {
            $this->em->persist($rev);
        }
        $this->em->persist($feedback);
        $this->em->flush();

        $user_category_id = $this->in->getUint('user_category_id');
        if ($user_category_id) {
            $field         = $this->_getUserCategoryField();
            $field_manager = $this->container->getSystemService('feedback_fields_manager');
            $field_manager->saveFormToObject(['field_'.$field->id => $user_category_id], $feedback, true);
        }

        return $this->createSuccessResponse();
    }

    /**
     * SWG\Api(
     * 	path="/feedback/{feedback_id}",
     * 	SWG\Operation(
     * 		method="DELETE",
     * 		summary="Deletes a Feedback by ID.",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="feedback_id",
     *				description="ID of the Feedback that needs to be deleted.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="Feedback not found")
     * 	)
     * ).
     */
    public function deleteFeedbackAction($feedback_id)
    {
        $feedback = $this->_getFeedbackOr404($feedback_id, 'delete');

        $feedback->status_code = 'hidden.deleted';
        $this->em->persist($feedback);
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * SWG\Api(
     * 	path="/feedback/{feedback_id}/votes",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Gets the votes for feedback",
     * 		notes="Information about the votes by Feedback ID.",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="feedback_id",
     *				description="ID of the Feedback that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="Feedback not found")
     * 	)
     * ).
     */
    public function getFeedbackVotesAction($feedback_id)
    {
        $feedback = $this->_getFeedbackOr404($feedback_id);
        $votes    = App::getEntityRepository('DeskPRO:Rating')->getRatingsFor('feedback', $feedback->id);

        return $this->createApiResponse(['votes' => $this->getApiData($votes)]);
    }

    /**
     * SWG\Api(
     * 	path="/feedback/{feedback_id}/comments",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Gets the comments for feedback",
     * 		notes="Information about the comments by Feedback ID.",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="feedback_id",
     *				description="ID of the Feedback that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="Feedback not found")
     * 	)
     * ).
     */
    public function getFeedbackCommentsAction($feedback_id)
    {
        $feedback = $this->_getFeedbackOr404($feedback_id);
        $comments = $this->em->getRepository('DeskPRO:FeedbackComment')->getComments($feedback);

        return $this->createApiResponse(['comments' => $this->getApiData($comments)]);
    }

    /**
     * SWG\Api(
     * 	path="/feedback/{feedback_id}/comments",
     * 	SWG\Operation(
     * 		method="POST",
     * 		summary="Add a comment for a feedback entry.",
     * 		notes="Creates a feedback comment by feedback ID.",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="feedback_id",
     *				description="ID of the feedback that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			SWG\Parameter(
     *				name="content",
     *				description="Text of the comment.",
     *				paramType="query",
     *				required=true,
     *				type="integer"
     *			),
     *			SWG\Parameter(
     *				name="person_id",
     *				description=" ID of the person that owns the comment. If not provided, defaults to the agent making the request.",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			),
     *			SWG\Parameter(
     *				name="status",
     *				description="Status of the comment. Defaults to visible.",
     *				paramType="query",
     *				required=false,
     *				type="string"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="Feedback not found")
     * 	)
     * ).
     */
    public function newFeedbackCommentAction($feedback_id)
    {
        $feedback = $this->_getFeedbackOr404($feedback_id);

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

        $comment                 = new \Application\DeskPRO\Entity\FeedbackComment();
        $comment->feedback       = $feedback;
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
                'api_feedback_feedback_comments_comment',
                ['feedback_id' => $feedback->id, 'comment_id' => $comment->id],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * SWG\Api(
     * 	path="/feedback/{feedback_id}/comments/{comment_id}",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Gets info about a specific feedback comment",
     * 		notes="Information about a specific feedback comment by Feedback ID and Comment ID.",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="feedback_id",
     *				description="ID of the Feedback that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			SWG\Parameter(
     *				name="comment_id",
     *				description="ID of the Feedback Comment that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="Feedback not found")
     * 	)
     * ).
     */
    public function getFeedbackCommentAction($feedback_id, $comment_id)
    {
        $feedback = $this->_getFeedbackOr404($feedback_id);
        $comment  = $this->em->getRepository('DeskPRO:FeedbackComment')->find($comment_id);
        if (!$comment || $comment->feedback->id != $feedback->id) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        return $this->createApiResponse(['comment' => $comment->toApiData()]);
    }

    /**
     * SWG\Api(
     * 	path="/feedback/{feedback_id}/comments/{comment_id}",
     * 	SWG\Operation(
     * 		method="POST",
     * 		summary="Updates a feedback comment",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="feedback_id",
     *				description="ID of the Feedback that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			SWG\Parameter(
     *				name="comment_id",
     *				description="ID of the Feedback Comment that needs to be updated.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			SWG\Parameter(
     *				name="content",
     *				description="New Text of the Comment.",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			),
     *			SWG\Parameter(
     *				name="status",
     *				description="Status of the comment.",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="Feedback not found")
     * 	)
     * ).
     */
    public function postFeedbackCommentAction($feedback_id, $comment_id)
    {
        $feedback = $this->_getFeedbackOr404($feedback_id);
        $comment  = $this->em->getRepository('DeskPRO:FeedbackComment')->find($comment_id);
        if (!$comment || $comment->feedback->id != $feedback->id) {
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
     * 	path="/feedback/{feedback_id}/comments/{comment_id}",
     * 	SWG\Operation(
     * 		method="DELETE",
     * 		summary="DELETE a specific feedback comment",
     * 		notes="DELETE a specific feedback comment by Feedback ID and Comment ID.",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="feedback_id",
     *				description="ID of the Feedback that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			SWG\Parameter(
     *				name="comment_id",
     *				description="ID of the Feedback Comment that needs to be deleted.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="Feedback not found")
     * 	)
     * ).
     */
    public function deleteFeedbackCommentAction($feedback_id, $comment_id)
    {
        $feedback = $this->_getFeedbackOr404($feedback_id);
        $comment  = $this->em->getRepository('DeskPRO:FeedbackComment')->find($comment_id);
        if (!$comment || $comment->feedback->id != $feedback->id) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        $this->em->remove($comment);
        $this->em->flush();

        $this->_sendCommentDeletedNotification($comment);

        return $this->createSuccessResponse();
    }

    /**
     * SWG\Api(
     * 	path="/feedback/{feedback_id}/merge/{other_feedback_id}",
     * 	SWG\Operation(
     * 		method="POST",
     * 		summary="Merges the two feedback records",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="feedback_id",
     *				description="ID of the first Feedback",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			SWG\Parameter(
     *				name="other_feedback_id",
     *				description="ID of the second Feedback",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="Feedback not found")
     * 	)
     * ).
     */
    public function mergeFeedbackAction($feedback_id, $other_feedback_id)
    {
        $feedback       = $this->_getFeedbackOr404($feedback_id, 'edit');
        $other_feedback = $this->_getFeedbackOr404($other_feedback_id, 'edit');

        if (!$this->person->PermissionsManager->PublishChecker->canEdit($feedback)
            || !$this->person->PermissionsManager->PublishChecker->canEdit($other_feedback)
            || !$this->person->PermissionsManager->PublishChecker->canDelete($other_feedback)
        ) {
            throw new AccessDeniedHttpException('Sorry, you do not have permission to perform this action');
        }

        try {
            $this->em->beginTransaction();
            $merge = new \Application\DeskPRO\Feedback\FeedbackMerge($this->person, $feedback, $other_feedback);
            $merge->merge();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();

            throw $e;
        }

        return $this->createSuccessResponse();
    }

    /**
     * SWG\Api(
     * 	path="/feedback/{feedback_id}/attachments",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Gets information about a feedback record's attachments",
     * 		notes="Information about a feedback record's attachments by Feedback ID.",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="feedback_id",
     *				description="ID of the Feedback that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="Feedback not found")
     * 	)
     * ).
     */
    public function getFeedbackAttachmentsAction($feedback_id)
    {
        $feedback = $this->_getFeedbackOr404($feedback_id);

        return $this->createApiResponse(['attachments' => $this->getApiData($feedback->attachments)]);
    }

    /**
     * SWG\Api(
     * 	path="/feedback/{feedback_id}/attachments",
     * 	SWG\Operation(
     * 		method="POST",
     * 		summary="Adds an attachment to a feedback record.",
     * 		notes="Adds an attachment to a feedback record by feedback ID.",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="feedback_id",
     *				description="ID of the feedback that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			SWG\Parameter(
     *				name="file",
     *				description="Attached file to include with the feedback. See the API Basics for more information on sending files to the API. Required if no attach_id is provided.",
     *				paramType="body",
     *				required=true,
     *				type="string"
     *			),
     *			SWG\Parameter(
     *				name="attach_id",
     *				description="The ID of an already uploaded file to include with the feedback. Required if no attach value is provided.",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="Feedback not found")
     * 	)
     * ).
     */
    public function newFeedbackAttachmentAction($feedback_id)
    {
        $feedback = $this->_getFeedbackOr404($feedback_id, 'edit');

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
                $message = $this->container->getTranslator()->phrase('agent.general.attach_error_'.$error['error_code'], $error);

                return $this->createApiErrorResponse($error['error_code'], $message);
            }
        } else {
            $blob_id = $this->in->getUint('attach_id');
            $blob    = $this->em->find('DeskPRO:Blob', $blob_id);
            if (!$blob) {
                return $this->createApiErrorResponse('invalid_argument.attach_id', 'attach_id not found');
            }
        }

        $attach = $this->_addFeedbackAttachment($blob, $feedback);

        $this->em->persist($feedback);
        $this->em->flush();

        return $this->createApiCreateResponse(
            ['id' => $attach->id],
            $this->generateUrl(
                'api_feedback_feedback_attachment',
                ['feedback_id' => $feedback->id, 'attachment_id' => $attach->id],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * SWG\Api(
     * 	path="/feedback/{feedback_id}/attachments/{attachment_id}",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Determines if a feedback record has an attachment",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="feedback_id",
     *				description="ID of the Feedback that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			SWG\Parameter(
     *				name="attachment_id",
     *				description="ID of the Feedback Comment that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="Feedback not found")
     * 	)
     * ).
     */
    public function getFeedbackAttachmentAction($feedback_id, $attachment_id)
    {
        $feedback = $this->_getFeedbackOr404($feedback_id);
        $exists   = false;
        foreach ($feedback->attachments as $attachment) {
            if ($attachment->id == $attachment_id) {
                $exists = true;
                break;
            }
        }

        return $this->createApiResponse(['exists' => $exists]);
    }

    /**
     * SWG\Api(
     * 	path="/feedback/{feedback_id}/attachments/{attachment_id}",
     * 	SWG\Operation(
     * 		method="DELETE",
     * 		summary="Removes a feedback attachment",
     * 		notes="Removes a feedback attachment by Feedback ID and Attachment ID.",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="feedback_id",
     *				description="ID of the Feedback that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			SWG\Parameter(
     *				name="attachment_id",
     *				description="ID of the Feedback Comment that needs to be deleted.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="Feedback not found")
     * 	)
     * ).
     */
    public function deleteFeedbackAttachmentAction($feedback_id, $attachment_id)
    {
        $feedback = $this->_getFeedbackOr404($feedback_id);
        foreach ($feedback->attachments as $k => $attachment) {
            if ($attachment->id == $attachment_id) {
                $feedback->attachments->remove($k);
                $this->em->remove($attachment);
                break;
            }
        }

        $this->em->persist($feedback);
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * SWG\Api(
     * 	path="/feedback/{feedback_id}/labels",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Gets the labels for feedback",
     * 		notes="Information about a feedback record's labels by Feedback ID.",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="feedback_id",
     *				description="ID of the Feedback that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="Feedback not found")
     * 	)
     * ).
     */
    public function getFeedbackLabelsAction($feedback_id)
    {
        $feedback = $this->_getFeedbackOr404($feedback_id);

        return $this->createApiResponse(['labels' => $this->getApiData($feedback->labels)]);
    }

    /**
     * SWG\Api(
     * 	path="/feedback/{feedback_id}/labels",
     * 	SWG\Operation(
     * 		method="POST",
     * 		summary="Add a label for a feedback entry.",
     * 		notes="Creates a feedback label by feedback ID.",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="feedback_id",
     *				description="ID of the feedback that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			SWG\Parameter(
     *				name="label",
     *				description="Label to add.",
     *				paramType="query",
     *				required=true,
     *				type="string"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="Feedback not found")
     * 	)
     * ).
     */
    public function postFeedbackLabelsAction($feedback_id)
    {
        $feedback = $this->_getFeedbackOr404($feedback_id, 'edit');
        $label    = $this->in->getString('label');

        if ($label === '') {
            return $this->createApiErrorResponse('required_field', "Field 'label' missing or empty");
        }

        $feedback->getLabelManager()->addLabel($label);
        $this->em->persist($feedback);
        $this->em->flush();

        return $this->createApiCreateResponse(
            ['label' => $label],
            $this->generateUrl(
                'api_feedback_feedback_label',
                ['feedback_id' => $feedback->id, 'label' => $label],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * SWG\Api(
     * 	path="/feedback/{feedback_id}/labels/{label}",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Determines if feedback has a label.",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="feedback_id",
     *				description="ID of the feedback that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			SWG\Parameter(
     *				name="label",
     *				description="Label to search for",
     *				paramType="path",
     *				required=true,
     *				type="string"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="Feedback not found")
     * 	)
     * ).
     */
    public function getFeedbackLabelAction($feedback_id, $label)
    {
        $feedback = $this->_getFeedbackOr404($feedback_id);

        if ($feedback->getLabelManager()->hasLabel($label)) {
            return $this->createApiResponse(['exists' => true]);
        } else {
            return $this->createApiResponse(['exists' => false]);
        }
    }

    /**
     * SWG\Api(
     * 	path="/feedback/{feedback_id}/labels/{label}",
     * 	SWG\Operation(
     * 		method="DELETE",
     * 		summary="Removes a label from feedback",
     *		SWG\Parameters (
     *			SWG\Parameter(
     *				name="feedback_id",
     *				description="ID of the feedback that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			SWG\Parameter(
     *				name="label",
     *				description="Label that needs to be deleted",
     *				paramType="path",
     *				required=true,
     *				type="string"
     *			)
     *		),
     *		SWG\ResponseMessage(code=404, message="Feedback not found")
     * 	)
     * ).
     */
    public function deleteFeedbackLabelAction($feedback_id, $label)
    {
        $feedback = $this->_getFeedbackOr404($feedback_id, 'edit');

        $feedback->getLabelManager()->removeLabel($label);
        $this->em->persist($feedback);
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * SWG\Api(
     * 	path="/feedback/validating-comments",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Gets feedback comments that are awaiting validation."
     * 	)
     * ).
     */
    public function getValidatingCommentsAction()
    {
        $comments   = $this->em->getRepository('DeskPRO:FeedbackComment')->getValidatingComments();
        $entity_key = 'feedback';
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
     * 	path="/feedback/categories",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Gets available feedback categories."
     * 	)
     * ).
     */
    public function getCategoriesAction()
    {
        $categories = $this->em->getRepository('DeskPRO:FeedbackCategory')->getFlatHierarchy();

        return $this->createApiResponse(['categories' => $categories]);
    }

    /**
     * SWG\Api(
     * 	path="/feedback/status-categories",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Gets available feedback status categories."
     * 	)
     * ).
     */
    public function getStatusCategoriesAction()
    {
        $categories = $this->em->getRepository('DeskPRO:FeedbackStatusCategory')->findAll();

        return $this->createApiResponse(['categories' => $this->getApiData($categories)]);
    }

    /**
     * SWG\Api(
     * 	path="/feedback/user-categories",
     * 	SWG\Operation(
     * 		method="GET",
     * 		summary="Gets available feedback user categories."
     * 	)
     * ).
     */
    public function getUserCategoriesAction()
    {
        $field    = $this->_getUserCategoryField();
        $children = $field->getAllChildren();

        return $this->createApiResponse(['categories' => $this->getApiData($children)]);
    }

    protected function _insertFeedbackAttachments(Feedback $feedback)
    {
        $attachments = $this->request->files->get('attach');
        if (!is_array($attachments)) {
            $attachments = [$attachments];
        }
        $accept = $this->container->getAttachmentAccepter();

        foreach ($attachments as $file) {
            $error = $accept->getError($file, 'agent');
            if (!$error) {
                $blob = $accept->accept($file);
                $this->_addFeedbackAttachment($blob, $feedback);
            }
        }

        foreach ($this->in->getCleanValueArray('attach_id') as $blob_id) {
            $this->_addFeedbackAttachment($blob_id, $feedback);
        }
    }

    protected function _addFeedbackAttachment($blob_id, Feedback $feedback)
    {
        if ($blob_id instanceof \Application\DeskPRO\Entity\Blob) {
            $blob = $blob_id;
        } else {
            $blob = $this->em->getRepository('DeskPRO:Blob')->find($blob_id);
        }

        if ($blob) {
            $attach           = new \Application\DeskPRO\Entity\FeedbackAttachment();
            $attach['blob']   = $blob;
            $attach['person'] = $this->person;

            $feedback->addAttachment($attach);

            return $attach;
        } else {
            return false;
        }
    }

    /**
     * @param Brand $brand
     *
     * @return \Application\DeskPRO\Entity\CustomDefFeedback
     */
    protected function _getUserCategoryField(Brand $brand = null)
    {
        if (!$brand) {
            $brand = $this->get('default_brand_finder')->getDefaultBrand();
        }

        return $this->container->getSystemService('feedback_categories')->getParentCategory($brand);
    }

    /**
     * @param int $id
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return \Application\DeskPRO\Entity\Feedback
     */
    protected function _getFeedbackOr404($id, $check_perm = false)
    {
        $feedback = $this->em->getRepository('DeskPRO:Feedback')->findOneById($id);

        if (!$feedback) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no feedback with ID $id");
        }

        if ($check_perm) {
            if ($check_perm == 'edit' && !$this->person->PermissionsManager->PublishChecker->canEdit($feedback)) {
                throw new AccessDeniedHttpException('Sorry, you do not have permission to perform this action');
            }

            if ($check_perm == 'delete' && !$this->person->PermissionsManager->PublishChecker->canDelete($feedback)) {
                throw new AccessDeniedHttpException('Sorry, you do not have permission to perform this action');
            }
        }

        return $feedback;
    }
}

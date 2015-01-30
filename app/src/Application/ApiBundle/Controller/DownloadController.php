<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\DownloadComment;
use Application\DeskPRO\Searcher\DownloadSearch;
use Orb\Util\Numbers;

/**
* @SWG\Resource(
* 	resourcePath="/downloads",
* 	description="Operations about Downloads",
* 	basePath="/api"
* )
*/
class DownloadController extends AbstractController
{
    /**
     * @SWG\Api(
     * 	path="/downloads",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Search for downloads matching criteria",
     * 		notes="Returns list of downloads that matched.",
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
     *				description="Requires the download to have been created before this date. Must be specified as a Unix timestamp.",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="date_created_start",
     *				description="Requires the download to have been created after this date. Must be specified as a Unix timestamp.",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="label[]",
     *				description="Requires the download to have this label.",
     *				paramType="query",
     *				required=false,
     *				type="string"
     *			),
     *			@SWG\Parameter(
     *				name="status[]",
     *				description="Requires the download to be in this status. Possible values: new, active, closed, hidden.",
     *				paramType="query",
     *				required=false,
     *				type="string"
     *			),
     *			@SWG\Parameter(
     *				name="new",
     *				description="If non-0, requires the download to be considered new (created within the last month). If 0, this does nothing.",
     *				paramType="query",
     *				required=false,
     *				type="boolean"
     *			),
     *			@SWG\Parameter(
     *				name="popular",
     *				description="If non-0, requires the download to be considered popular (50 or more downloads). If 0, this does nothing.",
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
            'category_id' => DownloadSearch::TERM_CATEGORY,
            'category_id_specific' => DownloadSearch::TERM_CATEGORY_SPECIFIC,
            'label' => DownloadSearch::TERM_LABEL,
            'new' => DownloadSearch::TERM_NEW,
            'popular' => DownloadSearch::TERM_POPULAR,
            'status' => DownloadSearch::TERM_STATUS
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
            $terms[] = array('type' => DownloadSearch::TERM_DATE_CREATED, 'op' => 'between', 'options' => array(
                'date1' => $date_created_start,
                'date2' => $date_created_end
            ));
        } elseif ($date_created_start) {
            $terms[] = array('type' => DownloadSearch::TERM_DATE_CREATED, 'op' => 'between', 'options' => array(
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

        $result_cache = $this->getApiSearchResult('download', $terms, $extra, $this->in->getUint('cache_id'), new DownloadSearch());

        $page = $this->in->getUint('page');
        if (!$page) $page = 1;

        $per_page = Numbers::bound($this->in->getUint('per_page') ?: 25, 1, 250);

        $ids = $result_cache->results;

        $page_ids = \Orb\Util\Arrays::getPageChunk($ids, $page, $per_page);
        $downloads = App::getEntityRepository('DeskPRO:Download')->getByIds($page_ids, true);

        return $this->createApiResponse(array(
            'page' => $page,
            'per_page' => $per_page,
            'total' => count($ids),
            'cache_id' => $result_cache->id,
            'downloads' => $this->getApiData($downloads)
        ));
    }

    /**
     * @SWG\Api(
     * 	path="/downloads",
     * 	@SWG\Operation(
     * 		method="POST",
     * 		summary="Creates a new download.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="title",
     *				description="Title of the download. ",
     *				paramType="query",
     *				required=true,
     *				type="string"
     *			),
     *			@SWG\Parameter(
     *				name="content",
     *				description="Content of the download. Marked up using HTML.",
     *				paramType="query",
     *				required=true,
     *				type="string"
     *			),
     *			@SWG\Parameter(
     *				name="category_id",
     *				description="Category of the download.",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="label[]",
     *				description="Comma seperated list of Labels to apply to the download.",
     *				paramType="query",
     *				required=false,
     *				type="string"
     *			),
     *			@SWG\Parameter(
     *				name="status",
     *				description="Status of the download. Defaults to new if not overridden by this or status_category_id.",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="attach",
     *				description="Attached file that represents the download. See the <a href='https://support.deskpro.com/downloads/articles/88-api-basics'>API Basics</a> for more information on sending files to the API. Required if no attach_id is provided.",
     *				paramType="body",
     *				required=true,
     *				type="string"
     *			),
     *			@SWG\Parameter(
     *				name="attach_id",
     *				description="The ID of an already uploaded file to include with the download. Required if no attach value is provided.",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			)
     *		)
     * 	)
     * )
     */
    public function newDownloadAction()
    {
        $errors = array();
        $download = new Download();

        $title = $this->in->getString('title');
        if ($title) {
            $download->title = $title;
        }

        $download->content = $this->in->getHtml('content');

        $status = $this->in->getString('status');
        if (!$status) {
            $status = 'published';
        }
        $download->setStatusCode($status);

        $cat = $this->em->find('DeskPRO:DownloadCategory', $this->in->getUint('category_id'));
        if (!$cat) {
            $errors['category_id'] = array('invalid_argument.category_id', 'category_id not found');
            } else {
            $download->category = $cat;
        }

        if ($errors) {
            return $this->createApiMultipleErrorResponse($errors);
        }

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
                $errors['attach'] = array($error['error_code'] . '.attach', $message);
            }
        } else {
            $blob_id = $this->in->getUint('attach_id');
            $blob = $this->em->find('DeskPRO:Blob', $blob_id);
            if (!$blob) {
                $errors['attach_id'] = array('invalid_argument.attach_id', 'attach_id not found');
            }
        }

        if ($errors) {
            return $this->createApiMultipleErrorResponse($errors);
        }

        $download->blob = $blob;
        if (!$download->title) {
            $download->title = $blob->filename;
        }
        $download->person = $this->person;

        $this->em->persist($blob);
        $this->em->persist($download);
        $this->em->flush();

        $labels = $this->in->getCleanValueArray('label', 'string', 'discard');
        if ($labels) {
            $download->getLabelManager()->setLabelsArray($labels, $this->em);
            $this->em->flush();
        }

        return $this->createApiCreateResponse(
            array('id' => $download->id),
            $this->generateUrl('api_downloads_download', array('download_id' => $download->id), true)
        );
    }

    /**
     * @SWG\Api(
     * 	path="/downloads/{download_id}",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Gets a download by download ID.",
     * 		notes="Information about the download by download ID.",
     *		type="Download",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="download_id",
     *				description="ID of the download that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Download not found")
     * 	)
     * )
     */
    public function getDownloadAction($download_id)
    {
        $download = $this->_getDownloadOr404($download_id);

        return $this->createApiResponse(array('download' => $download->toApiData()));
    }

    /**
     * @SWG\Api(
     * 	path="/downloads/{download_id}",
     * 	@SWG\Operation(
     * 		method="POST",
     * 		summary="Updates a Download by download ID.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="title",
     *				description="Title of the download. ",
     *				paramType="query",
     *				required=true,
     *				type="string"
     *			),
     *			@SWG\Parameter(
     *				name="content",
     *				description="Content of the download. Marked up using HTML.",
     *				paramType="query",
     *				required=true,
     *				type="string"
     *			),
     *			@SWG\Parameter(
     *				name="category_id",
     *				description="Category of the download.",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="label[]",
     *				description="Comma seperated list of Labels to apply to the download.",
     *				paramType="query",
     *				required=false,
     *				type="string"
     *			),
     *			@SWG\Parameter(
     *				name="status",
     *				description="Status of the download. Defaults to new if not overridden by this or status_category_id.",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="attach",
     *				description="Attached file that represents the download. See the <a href='https://support.deskpro.com/downloads/articles/88-api-basics'>API Basics</a> for more information on sending files to the API. Required if no attach_id is provided.",
     *				paramType="body",
     *				required=true,
     *				type="string"
     *			),
     *			@SWG\Parameter(
     *				name="attach_id",
     *				description="The ID of an already uploaded file to include with the download. Required if no attach value is provided.",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			)
     *		)
     * 	)
     * )
     */
    public function postDownloadAction($download_id)
    {
        $download = $this->_getDownloadOr404($download_id, 'edit');

        $revs = array();

        $title = $this->in->getString('title');
        if ($title) {
            $download->title = $title;

            $rev = ContentRevisionUtil::findOrCreate($download, 'title', $this->person);
            $rev->title = $download->title;

            $revs['title'] = $rev;
        }

        $status = $this->in->getString('status');
        if ($status) {
            $download->status = $status;
        }

        $content = $this->in->getString('content');
        if ($content && $content != $download->content) {
            $download->content = $this->in->getHtml('content');

            $rev = ContentRevisionUtil::findOrCreate($download, array('content'), $this->person);
            $rev->content = $download->content;

            $revs['content'] = $rev;
        }

        $file = $this->request->files->get('attach');
        if (is_array($file)) {
            $file = reset($file);
        }

        $blob = false;
        if ($file) {
            $accept = $this->container->getAttachmentAccepter();
            if (!$accept->getError($file, 'agent')) {
                $blob = $accept->accept($file);
            }
        } elseif ($this->in->getUint('attach_id')) {
            $blob = $this->em->find('DeskPRO:Blob', $this->in->getUint('attach_id'));
        }

        if ($blob) {
            if (!$title) {
                $download->title = $blob->filename;
            } else {
                // replace this revision below
                unset($revs['title']);
            }

            $download->blob = $blob;

            $rev = ContentRevisionUtil::findOrCreate($download, array('blob', 'title'), $this->person);
            $rev->title = $download->title;
            $rev->blob = $download->blob;

            $revs['file'] = $rev;
        }

        $category_id = $this->in->getUint('category_id');
        if ($category_id) {
            $cat = $this->em->find('DeskPRO:DownloadCategory', $category_id);
            if ($cat) {
                $download->category = $cat;
            }
        }

        foreach ($revs AS $rev) {
            $this->em->persist($rev);
        }
        $this->em->persist($download);
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * @SWG\Api(
     * 	path="/download/{download_id}",
     * 	@SWG\Operation(
     * 		method="DELETE",
     * 		summary="Deletes a Download by ID.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="download_id",
     *				description="ID of the Download that needs to be deleted.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Download not found")
     * 	)
     * )
     */
    public function deleteDownloadAction($download_id)
    {
        $download = $this->_getDownloadOr404($download_id, 'delete');

        $download->status_code = 'hidden.deleted';
        $this->em->persist($download);
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * @SWG\Api(
     * 	path="/download/{download_id}/comments",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Gets the comments for download",
     * 		notes="Information about the comments by Download ID.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="download_id",
     *				description="ID of the Download that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Download not found")
     * 	)
     * )
     */
    public function getDownloadCommentsAction($download_id)
    {
        $download = $this->_getDownloadOr404($download_id);
        $comments = $this->em->getRepository('DeskPRO:DownloadComment')->getComments($download);

        return $this->createApiResponse(array('comments' => $this->getApiData($comments)));
    }

    /**
     * @SWG\Api(
     * 	path="/download/{download_id}/comments",
     * 	@SWG\Operation(
     * 		method="POST",
     * 		summary="Add a comment for a download entry.",
     * 		notes="Creates a download comment by download ID.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="download_id",
     *				description="ID of the download that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="content",
     *				description="Text of the comment.",
     *				paramType="query",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="person_id",
     *				description=" ID of the person that owns the comment. If not provided, defaults to the agent making the request.",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="status",
     *				description="Status of the comment. Defaults to visible.",
     *				paramType="query",
     *				required=false,
     *				type="string"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Download not found")
     * 	)
     * )
     */
    public function newDownloadCommentAction($download_id)
    {
        $download = $this->_getDownloadOr404($download_id);

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

        $comment = new DownloadComment();
        $comment->download = $download;
        $comment->person = $person ?: $this->person;
        $comment['content'] = $content;
        $comment['status'] = $status ?: 'visible';
        $comment['is_reviewed'] = ($comment['status'] == 'visible' && !$person);
        $comment['date_created']  = new \DateTime();

        $this->em->persist($comment);
        $this->em->flush();

        return $this->createApiCreateResponse(
            array('id' => $comment->id),
            $this->generateUrl('api_downloads_download_comments_comment', array('download_id' => $download->id, 'comment_id' => $comment->id), true)
        );
    }

    /**
     * @SWG\Api(
     * 	path="/download/{download_id}/comments/{comment_id}",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Gets info about a specific download comment",
     * 		notes="Information about a specific download comment by Download ID and Comment ID.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="download_id",
     *				description="ID of the Download that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="comment_id",
     *				description="ID of the Download Comment that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Download not found")
     * 	)
     * )
     */
    public function getDownloadCommentAction($download_id, $comment_id)
    {
        $download = $this->_getDownloadOr404($download_id);
        $comment = $this->em->getRepository('DeskPRO:DownloadComment')->find($comment_id);
        if (!$comment || $comment->download->id != $download->id) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        return $this->createApiResponse(array('comment' => $comment->toApiData()));
    }

    /**
     * @SWG\Api(
     * 	path="/download/{download_id}/comments/{comment_id}",
     * 	@SWG\Operation(
     * 		method="POST",
     * 		summary="Updates a comment for a download entry.",
     * 		notes="Updates a download comment by download ID and comment ID",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="download_id",
     *				description="ID of the download that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="comment_id",
     *				description="ID of the download comment that needs to be updated.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="content",
     *				description="Text of the comment.",
     *				paramType="query",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="person_id",
     *				description=" ID of the person that owns the comment. If not provided, defaults to the agent making the request.",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="status",
     *				description="Status of the comment. Defaults to visible.",
     *				paramType="query",
     *				required=false,
     *				type="string"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Download not found")
     * 	)
     * )
     */
    public function postDownloadCommentAction($download_id, $comment_id)
    {
        $download = $this->_getDownloadOr404($download_id);
        $comment = $this->em->getRepository('DeskPRO:DownloadComment')->find($comment_id);
        if (!$comment || $comment->download->id != $download->id) {
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
     * 	path="/download/{download_id}/comments/{comment_id}",
     * 	@SWG\Operation(
     * 		method="DELETE",
     * 		summary="Deletes a comment for a download entry.",
     * 		notes="Deletes a download comment by download ID and comment ID",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="download_id",
     *				description="ID of the download that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="comment_id",
     *				description="ID of the download comment that needs to be deleted.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Download not found")
     * 	)
     * )
     */
    public function deleteDownloadCommentAction($download_id, $comment_id)
    {
        $download = $this->_getDownloadOr404($download_id);
        $comment = $this->em->getRepository('DeskPRO:DownloadComment')->find($comment_id);
        if (!$comment || $comment->download->id != $download->id) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        $this->em->remove($comment);
        $this->em->flush();

        $this->_sendCommentDeletedNotification($comment);

        return $this->createSuccessResponse();
    }

    /**
     * @SWG\Api(
     * 	path="/download/{download_id}/labels",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Gets the labels for download",
     * 		notes="Information about a download record's labels by Download ID.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="download_id",
     *				description="ID of the Download that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Download not found")
     * 	)
     * )
     */
    public function getDownloadLabelsAction($download_id)
    {
        $download = $this->_getDownloadOr404($download_id);

        return $this->createApiResponse(array('labels' => $this->getApiData($download->labels)));
    }

    /**
     * @SWG\Api(
     * 	path="/download/{download_id}/labels",
     * 	@SWG\Operation(
     * 		method="POST",
     * 		summary="Add a label for a download entry.",
     * 		notes="Creates a download label by download ID.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="download_id",
     *				description="ID of the download that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="label",
     *				description="Label to add.",
     *				paramType="query",
     *				required=true,
     *				type="string"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Download not found")
     * 	)
     * )
     */
    public function postDownloadLabelsAction($download_id)
    {
        $download = $this->_getDownloadOr404($download_id, 'edit');
        $label = $this->in->getString('label');

        if ($label === '') {
            return $this->createApiErrorResponse('required_field', "Field 'label' missing or empty");
        }

        $download->getLabelManager()->addLabel($label);
        $this->em->persist($download);
        $this->em->flush();

        return $this->createApiCreateResponse(
            array('label' => $label),
            $this->generateUrl('api_downloads_download_label', array('download_id' => $download->id, 'label' => $label), true)
        );
    }

    /**
     * @SWG\Api(
     * 	path="/download/{download_id}/labels/{label}",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Determines if download has a label.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="download_id",
     *				description="ID of the download that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="label",
     *				description="Label to search for",
     *				paramType="path",
     *				required=true,
     *				type="string"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Download not found")
     * 	)
     * )
     */
    public function getDownloadLabelAction($download_id, $label)
    {
        $download = $this->_getDownloadOr404($download_id);

        if ($download->getLabelManager()->hasLabel($label)) {
            return $this->createApiResponse(array('exists' => true));
        } else {
            return $this->createApiResponse(array('exists' => false));
        }
    }

    /**
     * @SWG\Api(
     * 	path="/download/{download_id}/labels/{label}",
     * 	@SWG\Operation(
     * 		method="DELETE",
     * 		summary="Removes a label from download",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="download_id",
     *				description="ID of the download that needs to be searched.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="label",
     *				description="Label that needs to be deleted",
     *				paramType="path",
     *				required=true,
     *				type="string"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Download not found")
     * 	)
     * )
     */
    public function deleteDownloadLabelAction($download_id, $label)
    {
        $download = $this->_getDownloadOr404($download_id, 'edit');

        $download->getLabelManager()->removeLabel($label);
        $this->em->persist($download);
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * @SWG\Api(
     * 	path="/download/validating-comments",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Gets download comments that are awaiting validation."
     * 	)
     * )
     */
    public function getValidatingCommentsAction()
    {
        $comments = $this->em->getRepository('DeskPRO:DownloadComment')->getValidatingComments();
        $entity_key = 'download';
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
     * 	path="/download/categories",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Gets available download categories."
     * 	)
     * )
     */
    public function getCategoriesAction()
    {
        $categories = $this->em->getRepository('DeskPRO:DownloadCategory')->getFlatHierarchy();

        return $this->createApiResponse(array('categories' => $categories));
    }

    /**
     * @SWG\Api(
     * 	path="/download/categories",
     * 	@SWG\Operation(
     * 		method="POST",
     * 		summary="Creates a download category.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="title",
     *				description="Title of the category that needs to be created.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="parent_id",
     *				description="ID of the category's parent. Use 0 for no parent.",
     *				paramType="query",
     *				required=true,
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
     *				description="comma separated IDs of usergroup that has access. If not provided, defaults to all users.",
     *				paramType="query",
     *				required=false,
     *				type="string"
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

        $category = new \Application\DeskPRO\Entity\DownloadCategory();

        $category->title = $title;

        $parent_id = $this->in->getUint('parent_id');
        if ($parent_id) {
            $parent = $this->em->getRepository('DeskPRO:DownloadCategory')->find($parent_id);
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
                App::getDb()->insert('download_category2usergroup', array(
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
            $this->generateUrl('api_downloads_category', array('category_id' => $category->id), true)
        );
    }

    /**
     * @SWG\Api(
     * 	path="/download/categories/{category_id}",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Gets a download category.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="category_id",
     *				description="ID of the category to look for.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Download Category not found")
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
     * 	path="/download/categories/{category_id}",
     * 	@SWG\Operation(
     * 		method="POST",
     * 		summary="Updates a download category.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="category_id",
     *				description="ID of the category to look for.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="title",
     *				description="New Title of the category.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="parent_id",
     *				description="New ID of the category's parent. Use 0 for no parent.",
     *				paramType="query",
     *				required=true,
     *				type="integer"
     *			),
     *			@SWG\Parameter(
     *				name="display_order",
     *				description="New Order of display of categories. Lower numbers will be displayed first.",
     *				paramType="query",
     *				required=false,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Download Category not found")
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
                $parent = $this->em->getRepository('DeskPRO:DownloadCategory')->find($parent_id);
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
     * 	path="/download/categories/{category_id}",
     * 	@SWG\Operation(
     * 		method="DELETE",
     * 		summary="DELETES a download category.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="category_id",
     *				description="ID of the category to look for.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Download Category not found")
     * 	)
     * )
     */
    public function deleteCategoryAction($category_id)
    {
        try {
            \Application\DeskPRO\Publish\CategoryEdit::deleteCategory('downloads', $category_id);
        } catch (\OutOfBoundsException $e) {
            return $this->createApiErrorResponse('invalid_argument.category_id', 'category is not empty');
        }

        return $this->createSuccessResponse();
    }

    /**
     * @SWG\Api(
     * 	path="/download/categories/{category_id}/downloads",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Gets downloads within a download category.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="category_id",
     *				description="ID of the category to look for.",
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
     *		@SWG\ResponseMessage(code=404, message="Download Category not found")
     * 	)
     * )
     */
    public function getCategoryDownloadsAction($category_id)
    {
        $category = $this->_getCategoryOr404($category_id);

        $terms = array(
            array('type' => DownloadSearch::TERM_CATEGORY_SPECIFIC, 'op' => 'contains', 'options' => array($category->id))
        );

        $order_by = $this->in->getString('order');
        if (!$order_by) {
            $order_by = 'date:desc';
        }

        $extra = array();
        if ($order_by !== null) {
            $extra['order_by'] = $order_by;
        }

        $result_cache = $this->getApiSearchResult('download', $terms, $extra, $this->in->getUint('cache_id'), new DownloadSearch());

        $page = $this->in->getUint('page');
        if (!$page) $page = 1;

        $per_page = Numbers::bound($this->in->getUint('per_page') ?: 25, 1, 250);

        $ids = $result_cache->results;

        $page_ids = \Orb\Util\Arrays::getPageChunk($ids, $page, $per_page);
        $downloads = App::getEntityRepository('DeskPRO:Download')->getByIds($page_ids, true);

        return $this->createApiResponse(array(
            'page' => $page,
            'per_page' => $per_page,
            'total' => count($ids),
            'cache_id' => $result_cache->id,
            'downloads' => $this->getApiData($downloads)
        ));
    }

    /**
     * @SWG\Api(
     * 	path="/download/categories/{category_id}/groups",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Gets groups with access to a download category.",
     *		@SWG\Parameters (
     *			@SWG\Parameter(
     *				name="category_id",
     *				description="ID of the category to look for.",
     *				paramType="path",
     *				required=true,
     *				type="integer"
     *			)
     *		),
     *		@SWG\ResponseMessage(code=404, message="Download Category not found")
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
     * 	path="/download/categories/{category_id}/groups",
     * 	@SWG\Operation(
     * 		method="POST",
     * 		summary="Adds a group to a download category.",
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
     *		@SWG\ResponseMessage(code=404, message="Download Category not found")
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
            $this->db->insert('download_category2usergroup', array(
                'category_id' => $category->id,
                'usergroup_id' => $group_id
            ));
        }

        return $this->createApiCreateResponse(
            array('id' => $group_id),
            $this->generateUrl('api_downloads_category_group', array('category_id' => $category->id, 'group_id' => $group_id), true)
        );
    }

    /**
     * @SWG\Api(
     * 	path="/download/categories/{category_id}/groups/{group_id}",
     * 	@SWG\Operation(
     * 		method="GET",
     * 		summary="Determines if a group has access to a download category.",
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
     *		@SWG\ResponseMessage(code=404, message="Download Category not found")
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
     * 	path="/download/categories/{category_id}/groups/{group_id}",
     * 	@SWG\Operation(
     * 		method="DELETE",
     * 		summary="Removes a group's access to a download category.",
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
     *		@SWG\ResponseMessage(code=404, message="Download Category not found")
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
     * @param  integer                                                       $id
     * @return \Application\DeskPRO\Entity\Download
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function _getDownloadOr404($id, $check_perm = false)
    {
        $download = $this->em->getRepository('DeskPRO:Download')->findOneById($id);

        if (!$download) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no download with ID $id");
        }

        if ($check_perm) {
            if ($check_perm == 'edit' && !$this->person->PermissionsManager->PublishChecker->canEdit($download)) {
                throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
            }

            if ($check_perm == 'delete' && !$this->person->PermissionsManager->PublishChecker->canDelete($download)) {
                throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
            }
        }

        return $download;
    }

    /**
     * @param  integer                                                       $id
     * @return \Application\DeskPRO\Entity\DownloadCategory
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function _getCategoryOr404($id)
    {
        $category = $this->em->getRepository('DeskPRO:DownloadCategory')->find($id);

        if (!$category) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no category with ID $id");
        }

        return $category;
    }
}

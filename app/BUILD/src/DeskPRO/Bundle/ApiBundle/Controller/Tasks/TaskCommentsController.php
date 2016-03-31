<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\ApiBundle\Controller\Tasks;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDocSection;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\OutputEntity;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\TaskComment;
use DeskPRO\Bundle\AppBundle\Form\Type\TaskCommentType;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TaskCommentsController.
 *
 * @ApiModes("all")
 * @Annotations\Route("/task_comments")
 * @ApiDocSection("Tasks")
 * @OutputEntity("DeskPRO\Bundle\AppBundle\Entity\TaskComment")
 */
class TaskCommentsController extends CrudController
{
    public static $entity    = TaskComment::class;
    public static $type      = TaskCommentType::class;
    public static $listSort  = 'date_created';
    public static $listOrder = 'asc';

    /**
     * @ApiDoc(
     *     section="Tasks",
     *     description="get attachments for a comment",
     *     requirements={
     *         {"name"="id", "requirement"="\d+", "description"="the id of the comment", "dataType"="integer"}
     *     },
     *     filters={
     *         {"name"="page", "pattern"="\d+", "description"="the page you are requesting", "dataType"="integer", "required"=false},
     *         {"name"="count", "pattern"="\d+", "description"="results per page", "dataType"="integer"}
     *     },
     *     statusCodes={
     *         200="Returned with fetched attachments list",
     *         404="Returned if we can't find task comment with given ID"
     *     },
     *     output="DeskPRO\Bundle\AppBundle\Entity\TaskAttachment"
     * )
     *
     * @Annotations\Get("/{id}/attachments", name="api_task_comments_attachments_get")
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function getAttachmentsAction(Request $request, $id)
    {
        /** @var TaskComment $comment */
        $comment = $this->findEntity($id, $request);

        if (empty($comment)) {
            throw $this->createNotFoundException();
        }

        $attachments = $comment->getAttachments();

        $page  = $request->query->get('page', 1);
        $count = $request->query->get('count', 10);

        $pager = new Pagerfanta(new ArrayAdapter($attachments->toArray()));
        $pager->setMaxPerPage($count);
        $pager->setCurrentPage($page);

        return View::create(
            $this->dataSerialize($pager),
            Response::HTTP_OK
        );
    }

    /**
     * @param Request $request
     *
     * @return TaskComment
     */
    protected function instantiateEntity(Request $request)
    {
        return new static::$entity($this->getUser());
    }
}

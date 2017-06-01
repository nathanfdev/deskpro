<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\Controller\Tasks;

use Application\DeskPRO\Entity\TaskComment;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudSubController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\Task\TaskCommentType;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TasksCommentsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/tasks/{parentId}/comments")
 * @ApiDoc(target="all", section="Tasks", output="Application\DeskPRO\Entity\TaskComment")
 *
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Task\TaskCommentType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\TaskComment",
 *          "person"="Application\DeskPRO\Entity\Person",
 *          "task"="DeskPRO\Bundle\AppBundle\Entity\Task"
 *      }
 *     }
 * )
 */
class TasksCommentsController extends CrudSubController
{
    public static $entity         = TaskComment::class;
    public static $type           = TaskCommentType::class;
    public static $listPaginate   = true;
    public static $listOrder      = 'DESC';
    public static $listSort       = 'date_created';
    public static $parentProperty = 'task';

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'person' => $this->getUser(),
            'task'   => $this->findParentOr404(),
        ]);

        return parent::handleForm($model, $request, $options);
    }

    protected function instantiateEntity(Request $request)
    {
        return new static::$entity($this->getUser(), '');
    }
}

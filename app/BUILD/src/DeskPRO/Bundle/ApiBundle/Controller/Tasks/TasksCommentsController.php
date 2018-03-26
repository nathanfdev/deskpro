<?php

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
 *          "task"="Application\DeskPRO\Entity\Task"
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

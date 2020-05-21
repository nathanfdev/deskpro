<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Tasks;

use Application\DeskPRO\Entity\Task;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\RequireAgentPermissions;
use DeskPRO\Bundle\AppBundle\Form\Type\Task\TaskType;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TasksController.
 *
 * @ApiModes("all")
 * @Rest\Route("/tasks")
 * @ApiDoc(
 *     target="all",
 *     section="Tasks",
 *     output="Application\DeskPRO\Entity\Task",
 *     input={
 *      "class"="Application\DeskPRO\Form\Type\TaskType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\Task",
 *          "person"="Application\DeskPRO\Entity\Person",
 *      }
 *     }
 * )
 * @RequireAgentPermissions()
 */
class TasksController extends CrudController
{
    public static $entity       = Task::class;
    public static $type         = TaskType::class;
    public static $listPaginate = true;
    public static $listOrder    = 'ASC';
    public static $listSort     = 'id';

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'person' => $this->getUser(),
        ]);

        return parent::handleForm($model, $request, $options);
    }
}

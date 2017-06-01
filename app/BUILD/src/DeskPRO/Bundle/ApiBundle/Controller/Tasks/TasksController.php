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

use Application\DeskPRO\Entity\Task;
use Application\DeskPRO\Form\Type\TaskType;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BrandsController.
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
 *          "tickets"="DeskPRO\Bundle\AppBundle\Entity\Ticket"
 *      }
 *     }
 * )
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

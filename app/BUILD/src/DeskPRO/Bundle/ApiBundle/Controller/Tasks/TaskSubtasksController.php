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

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDocSection;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\OutputEntity;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\TaskSubtask;
use DeskPRO\Bundle\AppBundle\Form\Type\TaskSubtaskType;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TaskSubtasksController.
 *
 * @Rest\Route("/subtasks")
 * @ApiDocSection("Tasks")
 * @OutputEntity("DeskPRO\Bundle\AppBundle\Entity\TaskSubtask")
 * @ApiModes("all")
 */
class TaskSubtasksController extends CrudController
{
    public static $entity    = TaskSubtask::class;
    public static $type      = TaskSubtaskType::class;
    public static $listOrder = 'asc';
    public static $listSort  = 'display_order';

    /**
     * @param Request $request
     *
     * @return TaskSubtask
     */
    protected function instantiateEntity(Request $request)
    {
        return new TaskSubtask($this->getUser());
    }
}

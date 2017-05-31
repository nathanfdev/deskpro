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
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BrandsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/tasks")
 * @ApiDoc(target="all", section="Tasks", output="Application\DeskPRO\Entity\Task")
 */
class TasksController extends CrudController
{
    public static $entity       = Task::class;
    public static $type         = TaskType::class;
    public static $listPaginate = true;
    public static $listOrder    = 'ASC';
    public static $listSort     = 'id';

    /**
     * This endpoint gives an ability to start chat with some person, team, department or with everyone in helpdesk.
     *
     * @ApiDoc(
     *     section = "Tasks",
     *     resourceDescription="Operations about tasks",
     *     description = "create a task",
     * )
     * @Rest\Post("")
     *
     * @param Request $request
     *
     * @throws InvalidFormException
     *
     * @return View
     */
    public function postAction(Request $request)
    {
        $form = $this->createForm(static::$type, $this->instantiateEntity($request), ['person' => $this->getUser()]);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $entity = $form->getData();
        $this->persistModel($entity, $form);

        $view = View::create($this->wrap($entity));
        $view->setLocation($this->getLocationUrl($entity, $request));

        return $view;
    }
}

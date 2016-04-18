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

namespace DeskPRO\Bundle\ApiBundle\Controller\Tasks;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\Task;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

/**
 * Class TaskLinkedController.
 *
 * @ApiModes("all")
 * @Rest\Route("/tasks")
 */
class TaskLinkedController extends BaseController
{
    /**
     * Get attached tickets for the task with specified id.
     *
     * @ApiDoc(
     *     section="Tasks",
     *     description="get attached tickets",
     *     requirements={
     *         {"name"="id", "requirement"="\d+", "description"="the id of the task", "dataType"="integer"}
     *     },
     *     statusCodes={
     *         200="Returned if you request was successful",
     *         404="Returned if task with given id was't found"
     *     },
     *     output="array<DeskPRO\Bundle\AppBundle\Entity\TaskLinkedItem\TaskLinkedTicket>"
     * )
     *
     * @Rest\Get("/{id}/linked_items/tickets")
     *
     * @param int $id
     *
     * @return View
     */
    public function getLinkedTicketsAction($id)
    {
        return $this->getLinked($id, 'tickets');
    }

    /**
     * Get attached chats for the task with specified id.
     *
     * @ApiDoc(
     *     section="Tasks",
     *     description="get attached chats",
     *     requirements={
     *         {"name"="id", "requirement"="\d+", "description"="the id of the task", "dataType"="integer"}
     *     },
     *     statusCodes={
     *         200="Returned if you request was successful",
     *         404="Returned if task with given id was't found"
     *     },
     *     input={"class"="task", "name"=""},
     *     output="array<DeskPRO\Bundle\AppBundle\Entity\TaskLinkedItem\TaskLinkedChat>"
     * )
     *
     * @Rest\Get("/{id}/linked_items/chats")
     *
     * @param int $id
     *
     * @return View
     */
    public function getLinkedChatsAction($id)
    {
        return $this->getLinked($id, 'chats');
    }

    /**
     * Get attached chats for the task with specified id.
     *
     * @ApiDoc(
     *     section="Tasks",
     *     description="get attached articles",
     *     section="Tasks",
     *     description="get attached links for a task",
     *     requirements={
     *         {"name"="id", "requirement"="\d+", "description"="the id of the task", "dataType"="integer"}
     *     },
     *     statusCodes={
     *         200="Returned if you request was successful",
     *         404="Returned if task with given id was't found"
     *     },
     *     output="array<DeskPRO\Bundle\AppBundle\Entity\TaskLinkedItem\TaskLinkedArticle>"
     * )
     *
     * @Rest\Get("/{id}/linked_items/articles")
     *
     * @param int $id
     *
     * @return View
     */
    public function getLinkedArticlesAction($id)
    {
        return $this->getLinked($id, 'articles');
    }

    /**
     * @param int    $id
     * @param string $type
     *
     * @return View
     */
    protected function getLinked($id, $type)
    {
        $task = $this->getManager()->getRepository(Task::class)->find($id);
        if (empty($task)) {
            throw $this->createNotFoundException();
        }

        $method = 'getLinked'.ucfirst($type);
        $links  = $task->$method();

        return View::create($this->wrap($links));
    }
}

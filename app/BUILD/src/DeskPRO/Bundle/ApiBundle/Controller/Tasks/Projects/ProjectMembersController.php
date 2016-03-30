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

namespace DeskPRO\Bundle\ApiBundle\Controller\Tasks\Projects;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDocSection;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\OutputEntity;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\ProjectMember;
use DeskPRO\Bundle\AppBundle\Form\Type\ProjectMemberType;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ProjectMembersController.
 *
 * @ApiDocSection("TaskProjects")
 * @OutputEntity("DeskPRO\Bundle\AppBundle\Entity\ProjectMember")
 * @Annotations\Route("/project_members")
 * @ApiModes("all")
 */
class ProjectMembersController extends CrudController
{
    public static $entity     = ProjectMember::class;
    public static $type       = ProjectMemberType::class;
    public static $exposeOnly = ['get', 'put', 'post', 'delete'];

    /**
     * Fetch tasks list for project member.
     *
     * @ApiDoc(
     *      section="TaskProjects",
     *      resourceDescription="Operations about project members",
     *      description="get tasks for a member",
     *      requirements={
     *          {"name"="id", "requirement"="\d+", "description"="the id of the member", "dataType"="integer"}
     *      },
     *      filters={
     *          {"name"="page", "pattern"="\d+", "description"="the page you are requesting", "dataType"="integer"},
     *          {"name"="count", "requirement"="\d+", "description"="results per page", "dataType"="integer"}
     *      },
     *      statusCodes={
     *          200="Everything is ok"
     *      },
     *      output="array<Application\DeskPRO\Entity\Task>"
     * )
     * @Annotations\Get("/{id}/tasks", name="api_project_members_tasks_get")
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function getTasksAction(Request $request, $id)
    {
        $id    = (int) $id;
        $tasks = $this->getDoctrine()->getManager()->getRepository('App:Task')->findBy(array('member' => $id));
        $page  = $request->query->get('page', 1);
        $count = $request->query->get('count', 10);

        $pager = new Pagerfanta(new ArrayAdapter($tasks));
        $pager->setMaxPerPage($count);
        $pager->setCurrentPage($page);

        return View::create(
            $this->wrap($pager),
            Response::HTTP_OK
        );
    }
}

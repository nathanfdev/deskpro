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
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\ProjectMember;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ProjectMembersController.
 *
 * @ApiModes("all")
 */
class ProjectMembersController extends BaseController implements ClassResourceInterface
{
    /**
     * Get a member with provided id.
     *
     * @ApiDoc(
     *      section="Tasks",
     *      resourceDescription="Operations about tasks",
     *      description="get a member",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the member",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Returned in case of success",
     *          404="Returned if member was not found"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\ProjectMember"
     * )
     * @Get("/project_members/{id}", name="api_project_members_get")
     *
     * @param int $id
     *
     * @return View
     */
    public function getAction($id)
    {
        $member = $this->getProjectMember($id);

        if (empty($member)) {
            throw $this->createNotFoundException();
        }

        return View::create(
            $this->dataSerialize($member),
            Response::HTTP_OK
        );
    }

    /**
     * Create a project member. Note that only person, team or department would be attached as member.
     *
     * Team takes precedence on person
     * Department takes precedence on team
     *
     * @ApiDoc(
     *      section="Tasks",
     *      resourceDescription="Operations about tasks",
     *      description="create a new member",
     *      requirements={
     *          {"name"="person", "requirement"="\d+", "description"="the id of the person", "dataType"="integer"},
     *          {"name"="department", "requirement"="\d+", "description"="the id of the department", "dataType"="integer"},
     *          {"name"="team", "requirement"="\d+", "description"="the id of the team", "dataType"="integer"},
     *          {"name"="project", "requirement"="\d+", "description"="the id of the project", "dataType"="integer"}
     *      },
     *      statusCodes={
     *          201="Returned if project member was successfully added",
     *          400="Your request was malformed"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\ProjectMember"
     * )
     * @Post("/project_members", name="api_project_members_post")
     *
     * @param Request $request
     *
     * @throws InvalidFormException
     *
     * @return View
     */
    public function postAction(Request $request)
    {
        $member = new ProjectMember();

        return $this->handleFormSubmission($request, $member);
    }

    /**
     * Change member entry. You can change it between person, department or team.
     *
     * Team takes precedence on person
     * Department takes precedence on team
     *
     * @APIDoc(
     *      section="Tasks",
     *      resourceDescription="Operations about tasks",
     *      description="update a member",
     *      requirements={
     *          {"name"="id", "requirement"="\d+", "description"="the id of the member", "dataType"="integer"},
     *          {"name"="person", "requirement"="\d+", "description"="the id of the person", "dataType"="integer"},
     *          {"name"="department", "requirement"="\d+", "description"="the id of the department", "dataType"="integer"},
     *          {"name"="team", "requirement"="\d+", "description"="the id of the team", "dataType"="integer"},
     *          {"name"="project", "requirement"="\d+", "description"="the id of the project", "dataType"="integer"}
     *      },
     *      statusCodes={
     *          204="Returned if member was successfully updated",
     *          400="Request was malformed",
     *          404="Member with specified id was not found"
     *      }
     * )
     *
     * @Put("/project_members/{id}", name="api_project_members_put")
     *
     * @param Request $request
     * @param $id
     *
     * @throws InvalidFormException
     *
     * @return View
     */
    public function putAction(Request $request, $id)
    {
        $member = $this->getProjectMember($id);

        return $this->handleFormSubmission($request, $member);
    }

    /**
     * Delete the member with given id.
     *
     * @APIDoc(
     *      section="Tasks",
     *      resourceDescription="Operations about tasks",
     *      description="delete the member",
     *      requirements={
     *          {"name"="id", "requirement"="\d+", "description"="the id of the member", "dataType"="integer"}
     *      },
     *      statusCodes={
     *          200="We had deleted member you asked",
     *          404="We can't find member with given ID"
     *      }
     * )
     * @Delete("/project_members/{id}", name="api_projectmembers_delete")
     *
     * @param $id
     *
     * @return View
     */
    public function deleteAction($id)
    {
        $member = $this->getProjectMember($id);
        $this->getDoctrine()->getManager()->remove($member);
        $this->getDoctrine()->getManager()->flush();

        return View::create(
            null,
            Response::HTTP_OK
        );
    }

    /**
     * Fetch tasks list for project member.
     *
     * @APIDoc(
     *      section="Tasks",
     *      resourceDescription="Operations about tasks",
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
     * @Get("/project_members/{id}/tasks", name="api_project_members_tasks_get")
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

    /**
     * @param int $id
     *
     * @return ProjectMember
     */
    protected function getProjectMember($id)
    {
        $id     = (int) $id;
        $member = $this->getDoctrine()->getManager()->getRepository('App:ProjectMember')->find($id);

        if (!$member) {
            throw $this->createNotFoundException();
        }

        return $member;
    }

    /**
     * Will be abstracted for use by other controllers.
     *
     * @param Request       $request
     * @param ProjectMember $member
     *
     * @throws InvalidFormException
     *
     * @return View
     */
    protected function handleFormSubmission(Request $request, ProjectMember $member)
    {
        $update = (bool) $member->getId();

        $status = $update ? Response::HTTP_NO_CONTENT : Response::HTTP_CREATED;

        /** @var Form $form */
        $form = $this->get('form.factory')->createNamedBuilder(null, 'projectmember', $member)->getForm();

        $submitted = $request->request->all();
        $submitted = $this->cleanMemberTypes($submitted);

        $form->submit($submitted, $request->getMethod() !== 'PUT');

        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->persist($member);
            $this->getDoctrine()->getManager()->flush();

            $location = $this->generateUrl('api_project_members_get', array('id' => $member->getId()));

            if ($update) {
                return View::create(null, $status);
            } else {
                return View::create($this->wrap($member),
                $status,
                ['Location' => $location]);
            }
        }

        throw new InvalidFormException($form);
    }

    /**
     * Cleans up the submitted array so that we only have one person, team or department.
     *
     * @param array $submitted
     *
     * @return array
     */
    private function cleanMemberTypes(array $submitted)
    {
        $types   = array('person', 'team', 'department');
        $cleaned = false;

        foreach ($types as $type) {
            if (false === $cleaned && !empty($submitted[$type])) {
                $cleaned = true;
            } else {
                $submitted[$type] = '';
            }
        }

        return $submitted;
    }
}

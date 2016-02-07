<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Exception\WrappedApiErrorException;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\ProjectMember;
use DeskPRO\Bundle\AppBundle\Error\Exception\InvalidFormException;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
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
     * @ApiDoc(
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
     *          200="Success",
     *          404="Not Found"
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
     * @ApiDoc(
     *      description="create a new member",
     *      input={"class"="member", "name"=""},
     *      statusCodes={
     *          201="Created",
     *          400="Bad Request"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\ProjectMember"
     * )
     * @Post("/project_members", name="api_project_members_post")
     *
     * @param Request $request
     *
     * @throws WrappedApiErrorException
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
     * @APIDoc(
     *      description="update a member",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the member",
     *              "dataType"="integer"
     *          }
     *      },
     *      input={"class"="member", "name"=""},
     *      statusCodes={
     *          204="Updated",
     *          400="Bad Request",
     *          404="Not Found"
     *      }
     * )
     *
     * @Put("/project_members/{id}", name="api_project_members_put")
     *
     * @param Request $request
     * @param $id
     *
     * @throws WrappedApiErrorException
     *
     * @return View
     */
    public function putAction(Request $request, $id)
    {
        $member = $this->getProjectMember($id);

        return $this->handleFormSubmission($request, $member);
    }

    /**
     * @APIDoc(
     *      description="delete a member",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the member",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
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
            array(),
            Response::HTTP_OK
        );
    }

    /**
     * @APIDoc(
     *      description="get tasks for a member",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the member",
     *              "dataType"="integer"
     *          }
     *      },
     *      parameters={
     *          {
     *              "name"="page",
     *              "requirement"="\d+",
     *              "description"="the page you are requesting",
     *              "dataType"="integer",
     *              "required"=false
     *          },
     *          {
     *              "name"="count",
     *              "requirement"="\d+",
     *              "description"="results per page",
     *              "dataType"="integer",
     *              "required"=false
     *          }
     *      },
     *      statusCodes={
     *          200="Success"
     *      }
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
            $this->dataSerialize($pager),
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
     * @throws WrappedApiErrorException
     *
     * @return View
     */
    protected function handleFormSubmission(Request $request, ProjectMember $member)
    {
        $status = $member->getId() ? Response::HTTP_NO_CONTENT : Response::HTTP_CREATED;

        /** @var Form $form */
        $form = $this->get('form.factory')->createNamedBuilder(null, 'projectmember', $member)->getForm();

        $submitted = $request->request->all();
        $submitted = $this->cleanMemberTypes($submitted);

        $form->submit($submitted, $request->getMethod() !== 'PUT');

        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->persist($member);
            $this->getDoctrine()->getManager()->flush();

            $location = $this->generateUrl('api_project_members_get', array('id' => $member->getId()));

            return View::create(
                $this->dataSerialize($member),
                $status,
                array(
                    'Location' => $location,
                )
            );
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

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
use DeskPRO\Bundle\AppBundle\Entity\TaskProject as Project;
use DeskPRO\Bundle\AppBundle\Error\Exception\InvalidFormException;
use Doctrine\ORM\QueryBuilder;
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
 * Class ProjectsController.
 *
 * @ApiModes("all")
 */
class ProjectsController extends BaseController implements ClassResourceInterface
{
    /**
     * @ApiDoc(
     *      description="get a list of projects",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Get("/projects", name="api_projects")
     *
     * @param Request $request
     *
     * @return View
     */
    public function cgetAction(Request $request)
    {
        $query = $request->query->all();

        $projectIds = !empty($query['ids']) ? explode(',', $query['ids']) : [];
        $projects   = $this->selectProjects($projectIds);
        $projects   = $projects->getResult();

        return View::create($this->dataSerialize($projects), Response::HTTP_OK);
    }

    /**
     * @ApiDoc(
     *      description="get a project",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the project",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\TaskProject"
     * )
     * @Get("/projects/{id}", name="api_projects_get")
     *
     * @param int $id
     *
     * @return View
     */
    public function getAction($id)
    {
        $project = $this->getProject($id);
        if (empty($project)) {
            throw $this->createNotFoundException();
        }

        return View::create($this->dataSerialize($project), Response::HTTP_OK);
    }

    /**
     * @ApiDoc(
     *      description="create a new project",
     *      input={"class"="project", "name"=""},
     *      statusCodes={
     *          201="Created",
     *          400="Bad Request"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\TaskProject"
     * )
     * @Post("/projects", name="api_projects_post")
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
        $project = new Project();

        return $this->handleFormSubmission($request, $project);
    }

    /**
     * @APIDoc(
     *      description="update a project",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the project",
     *              "dataType"="integer"
     *          }
     *      },
     *      input={"class"="project", "name"=""},
     *      statusCodes={
     *          204="Updated",
     *          400="Bad Request",
     *          404="Not Found"
     *      }
     * )
     * @Put("/projects/{id}", name="api_projects_put")
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
        $project = $this->getProject($id);

        return $this->handleFormSubmission($request, $project);
    }

    /**
     * @APIDoc(
     *      description="delete a project",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the project",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      }
     * )
     * @Delete("/projects/{id}", name="api_projects_delete")
     *
     * @param $id
     *
     * @return View
     */
    public function deleteAction($id)
    {
        $project = $this->getProject($id);

        if (!$project) {
            throw $this->createNotFoundException();
        }

        $this->getDoctrine()->getManager()->remove($project);
        $this->getDoctrine()->getManager()->flush();

        return View::create([], Response::HTTP_OK);
    }

    /**
     * @APIDoc(
     *      description="get tasks for a project",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the project",
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
     * @Get("/projects/{id}/tasks", name="api_projects_tasks_get")
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function getTasksAction(Request $request, $id)
    {
        $id    = (int) $id;
        $tasks = $this->getDoctrine()->getManager()->getRepository('App:Task')->findBy(['project' => $id]);
        $page  = $request->query->get('page', 1);
        $count = $request->query->get('count', 10);

        $pager = new Pagerfanta(new ArrayAdapter($tasks));
        $pager->setMaxPerPage($count);
        $pager->setCurrentPage($page);

        return View::create($this->dataSerialize($pager), Response::HTTP_OK);
    }

    /**
     * @APIDoc(
     *      description="get departments for a project",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the project",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Get("/projects/{id}/departments", name="api_projects_departments_get")
     *
     * @param int $id
     *
     * @return View
     */
    public function getDepartmentsAction($id)
    {
        $query = $this->getProjectMemberQuery((int) $id, 'DeskPRO:Department');

        return View::create($this->dataSerialize($query->getArrayResult()), Response::HTTP_OK);
    }

    /**
     * @APIDoc(
     *      description="get teams for a project",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the project",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Get("/projects/{id}/teams", name="api_projects_teams_get")
     *
     * @param int $id
     *
     * @return View
     */
    public function getTeamsAction($id)
    {
        $query = $this->getProjectMemberQuery((int) $id, 'DeskPRO:AgentTeam');

        return View::create($this->dataSerialize($query->getArrayResult()), Response::HTTP_OK);
    }

    /**
     * @APIDoc(
     *      description="get agents for a project",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the project",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Get("/projects/{id}/agents", name="api_projects_agents_get")
     *
     * @param int $id
     *
     * @return View
     */
    public function getAgentsAction($id)
    {
        $query = $this->getProjectMemberQuery((int) $id, 'DeskPRO:Person');

        return View::create($this->dataSerialize($query->getArrayResult()), Response::HTTP_OK);
    }

    /**
     * @ApiDoc(
     *      description="add a department as a project member",
     *      input={"class"="project", "id"=""},
     *      statusCodes={
     *          201="Created",
     *          400="Bad Request"
     *      },
     *      output="Application\DeskPRO\Entity\Department"
     * )
     * @Post("/projects/{id}/departments", name="api_projects_departments_post")
     *
     * @param Request $request
     * @param $id
     *
     * @return View
     */
    public function postDepartmentAction(Request $request, $id)
    {
        return $this->postMember($request, $id, 'department');
    }

    /**
     * @ApiDoc(
     *      description="add an agent team as a project member",
     *      input={"class"="project", "id"=""},
     *      statusCodes={
     *          201="Created",
     *          400="Bad Request"
     *      },
     *      output="Application\DeskPRO\Entity\AgentTeam"
     * )
     * @Post("/projects/{id}/teams", name="api_projects_teams_post")
     *
     * @param Request $request
     * @param $id
     *
     * @return View
     */
    public function postTeamAction(Request $request, $id)
    {
        return $this->postMember($request, $id, 'team');
    }

    /**
     * @ApiDoc(
     *      description="add an agent as a project member",
     *      input={"class"="project", "id"=""},
     *      statusCodes={
     *          201="Created",
     *          400="Bad Request"
     *      },
     *      output="Application\DeskPRO\Entity\Person"
     * )
     * @Post("/projects/{id}/agents", name="api_projects_agents_post")
     *
     * @param Request $request
     * @param $id
     *
     * @return View
     */
    public function postAgentAction(Request $request, $id)
    {
        return $this->postMember($request, $id, 'person');
    }

    /**
     * @APIDoc(
     *      description="remove a relationship between a project and a department",
     *      requirements={
     *          {
     *              "name"="projectId",
     *              "requirement"="\d+",
     *              "description"="the id of the project",
     *              "dataType"="integer"
     *          },
     *          {
     *              "name"="deptId",
     *              "requirement"="\d+",
     *              "description"="the id of the department",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      }
     * )
     * @Delete("/projects/{projectId}/departments/{deptId}", name="api_projects_department_delete")
     *
     * @param $projectId
     * @param $deptId
     *
     * @return View
     */
    public function deleteDepartmentAction($projectId, $deptId)
    {
        $member = $this->getDoctrine()->getManager()->getRepository('App:ProjectMember')->findOneBy([
            'department' => (int) $deptId,
            'project'    => (int) $projectId,
        ]);

        return $this->deleteMember($member);
    }

    /**
     * @APIDoc(
     *      description="remove a relationship between a project and an agent team",
     *      requirements={
     *          {
     *              "name"="projectId",
     *              "requirement"="\d+",
     *              "description"="the id of the project",
     *              "dataType"="integer"
     *          },
     *          {
     *              "name"="teamId",
     *              "requirement"="\d+",
     *              "description"="the id of the team",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      }
     * )
     * @Delete("/projects/{projectId}/departments/{teamId}", name="api_projects_team_delete")
     *
     * @param $projectId
     * @param $teamId
     *
     * @return View
     */
    public function deleteTeamAction($projectId, $teamId)
    {
        $member = $this->getDoctrine()->getManager()->getRepository('App:ProjectMember')->findOneBy([
            'team'    => (int) $teamId,
            'project' => (int) $projectId,
        ]);

        return $this->deleteMember($member);
    }

    /**
     * @APIDoc(
     *      description="remove a relationship between a project and an agent",
     *      requirements={
     *          {
     *              "name"="projectId",
     *              "requirement"="\d+",
     *              "description"="the id of the project",
     *              "dataType"="integer"
     *          },
     *          {
     *              "name"="personId",
     *              "requirement"="\d+",
     *              "description"="the id of the agent",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      }
     * )
     * @Delete("/projects/{projectId}/departments/{personId}", name="api_projects_agent_delete")
     *
     * @param $projectId
     * @param $personId
     *
     * @return View
     */
    public function deleteAgentAction($projectId, $personId)
    {
        $member = $this->getDoctrine()->getManager()->getRepository('App:ProjectMember')->findOneBy([
            'department' => (int) $personId,
            'project'    => (int) $projectId,
        ]);

        return $this->deleteMember($member);
    }

    /**
     * @APIDoc(
     *      description="get lists for a project",
     *      requirements={
     *          {
     *              "name"="projectId",
     *              "requirement"="\d+",
     *              "description"="the id of the project",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Get("/projects/{projectId}/lists", name="api_projects_lists_get")
     *
     * @param $projectId
     *
     * @return View
     */
    public function getListsAction($projectId)
    {
        $project = $this->getProject($projectId);
        if (empty($project)) {
            throw $this->createNotFoundException();
        }

        $lists = $project->getLists();

        return View::create($this->dataSerialize($lists), Response::HTTP_OK);
    }

    /**
     * Create a new member relationship for a department, team or person.
     *
     * @param Request $request
     * @param $projectId
     * @param $type
     *
     * @return View
     */
    protected function postMember(Request $request, $projectId, $type)
    {
        $memberRepositories = [
            'department' => 'DeskPRO:Department',
            'team'       => 'DeskPRO:AgentTeam',
            'person'     => 'DeskPRO:Person',
        ];

        if (!in_array($type, array_keys($memberRepositories))) {
            throw new \InvalidArgumentException();
        }

        $project = $this->getProject($projectId);

        $submitted = $request->request->all();

        $objectId = $submitted['id'];
        $object   = $this->getDoctrine()->getManager()->getRepository($memberRepositories[$type])->find($objectId);

        if (!$object) {
            throw $this->createNotFoundException();
        }

        $member = new ProjectMember();
        $member->setProject($project);
        $setter = 'set'.ucfirst($type);
        $member->$setter($object);

        $validate = [
            'project' => $projectId,
            $type     => $objectId,
        ];

        /** @var Form $form */
        $form = $this->get('form.factory')->createNamedBuilder(null, 'projectmember', $member)->getForm();
        $form->submit($validate, true);

        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->persist($member);
            $this->getDoctrine()->getManager()->flush();

            $location = $this->generateUrl('api_project_members_get', ['id' => $member->getId()]);

            return View::create(
                $this->dataSerialize($object),
                Response::HTTP_CREATED,
                [
                    'Location' => $location,
                ]
            );
        }

        throw new InvalidFormException($form);
    }

    /**
     * Delete a project member relationship.
     *
     * @param $member
     *
     * @return View
     */
    protected function deleteMember($member)
    {
        if (!$member) {
            throw $this->createNotFoundException();
        }

        $this->getDoctrine()->getManager()->remove($member);
        $this->getDoctrine()->getManager()->flush();

        return View::create([], Response::HTTP_OK);
    }

    /**
     * Retrieve a single project.
     *
     * @param int $id
     *
     * @return Project
     */
    protected function getProject($id)
    {
        $project = $this->getDoctrine()->getManager()->getRepository('App:TaskProject')->find((int) $id);
        if (!$project) {
            throw $this->createNotFoundException();
        }

        return $project;
    }

    /**
     * Will be abstracted for use by other controllers.
     *
     * @param Request $request
     * @param Project $project
     *
     * @throws WrappedApiErrorException
     *
     * @return View
     */
    protected function handleFormSubmission(Request $request, Project $project)
    {
        $status = $project->getId() ? Response::HTTP_NO_CONTENT : Response::HTTP_CREATED;

        $submitted = $request->request->all();

        /** @var Form $form */
        $form = $this->get('form.factory')->createNamedBuilder(null, 'project', $project)->getForm();
        $form->submit($submitted, $request->getMethod() !== 'PUT');

        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->persist($project);
            $this->getDoctrine()->getManager()->flush();

            $location = $this->generateUrl('api_projects_get', ['id' => $project->getId()]);

            return View::create(
                $this->dataSerialize($project),
                $status,
                [
                    'Location' => $location,
                ]
            );
        }

        throw new InvalidFormException($form);
    }

    /**
     * Get the Doctrine Query for a project member.
     *
     * @param $projectId
     * @param $object
     *
     * @return \Doctrine\ORM\Query
     */
    protected function getProjectMemberQuery($projectId, $object)
    {
        /* @var QueryBuilder $queryBuilder */
        $entityManager = $this->getDoctrine()->getManager();
        $queryBuilder  = $entityManager->createQueryBuilder()->select('d')->from($object, 'd')
            ->leftJoin('d.project_members', 'p')
            ->where('p.project = :project')
            ->setParameter('project', $projectId);

        return $queryBuilder->getQuery();
    }

    /**
     * Get a Doctrine Query for getting certain projects.
     *
     * @param $projectIds
     *
     * @return \Doctrine\ORM\Query
     */
    protected function selectProjects($projectIds = [])
    {
        $entityManager = $this->getDoctrine()->getManager();

        // Clean the IDs
        $projectIds = array_map(function ($value) {
            return (int) $value;
        }, $projectIds);

        $query = $entityManager->createQueryBuilder()->select('p')->from('App:TaskProject', 'p');

        if (!empty($projectIds)) {
            $query = $query->where('p.id IN (:projectIds)')
                ->setParameter('projectIds', $projectIds);
        }

        $query = $query->orderBy('p.title', 'ASC');

        return $query->getQuery();
    }
}

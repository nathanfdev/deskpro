<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\ApiBundle\Controller\Tasks;

use DeskPRO\Bundle\AppBundle\Entity\ProjectMember;
use Doctrine\ORM\QueryBuilder;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\ApiBundle\Exception\WrappedApiErrorException;
use DeskPRO\Bundle\AppBundle\Entity\TaskProject as Project;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Delete;

class ProjectsController extends BaseController implements ClassResourceInterface
{
    private $storedMembers = array();
    private $oldMembers = array();

    /**
     * @ApiDoc(
     *      description="get a list of projects",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Get("/projects", name="api_projects")
     * @param Request $request
     * @return View
     */
    public function cgetAction(Request $request)
    {
        $projects = $this->getDoctrine()->getManager()->getRepository('App:TaskProject')->findAll();

        return View::create(
            $this->dataSerialize($projects),
            Response::HTTP_OK
        );
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
     * @param int $projectId
     * @return View
     */
    public function getAction($id)
    {
        $project = $this->getProject($id);

        if (empty($project)) {
            throw $this->createNotFoundException();
        }

        return View::create(
            $this->createRepresentation($project),
            Response::HTTP_OK
        );
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
     * @param Request $request
     * @throws WrappedApiErrorException
     * @throws InvalidFormException
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
     * @param Request $request
     * @param $id
     * @throws WrappedApiErrorException
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
     * @param $id
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

        return View::create(
            array(),
            Response::HTTP_OK
        );
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
     * @param Request $request
     * @param int $id
     * @return View
     */
    public function getTasksAction(Request $request, $id)
    {
        $id = (int) $id;
        $tasks = $this->getDoctrine()->getManager()->getRepository('App:Task')->findBy(array('project' => $id));
        $page = $request->query->get('page', 1);
        $count = $request->query->get('count', 10);

        $pager = new Pagerfanta(new ArrayAdapter($tasks));
        $pager->setMaxPerPage($count);
        $pager->setCurrentPage($page);

        return View::create(
            $this->createRepresentation($pager),
            Response::HTTP_OK
        );
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
     * @param int $id
     * @return View
     */
    public function getDepartmentsAction($id)
    {
        $id = (int) $id;

        $query = $this->getProjectMemberQuery($id, 'DeskPRO:Department');

        return View::create(
            $this->createRepresentation($query->getArrayResult()),
            Response::HTTP_OK
        );
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
     * @param int $id
     * @return View
     */
    public function getTeamsAction($id)
    {
        $id = (int) $id;

        $query = $this->getProjectMemberQuery($id, 'DeskPRO:AgentTeam');

        return View::create(
            $this->createRepresentation($query->getArrayResult()),
            Response::HTTP_OK
        );
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
     * @param int $id
     * @return View
     */
    public function getAgentsAction($id)
    {
        $id = (int) $id;

        $query = $this->getProjectMemberQuery($id, 'DeskPRO:Person');

        return View::create(
            $this->createRepresentation($query->getArrayResult()),
            Response::HTTP_OK
        );
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
     * @param Request $request
     * @param $id
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
     * @param Request $request
     * @param $id
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
     * @param Request $request
     * @param $id
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
     * @param $projectId
     * @param $deptId
     * @return View
     */
    public function deleteDepartmentAction($projectId, $deptId)
    {
        $member = $this->getDoctrine()->getManager()->getRepository('App:ProjectMember')->findOneBy([
            'department' => (int) $deptId,
            'project' => (int) $projectId,
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
     * @param $projectId
     * @param $teamId
     * @return View
     */
    public function deleteTeamAction($projectId, $teamId)
    {
        $member = $this->getDoctrine()->getManager()->getRepository('App:ProjectMember')->findOneBy([
            'team' => (int) $teamId,
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
     * @param $projectId
     * @param $personId
     * @return View
     */
    public function deleteAgentAction($projectId, $personId)
    {
        $member = $this->getDoctrine()->getManager()->getRepository('App:ProjectMember')->findOneBy([
            'department' => (int) $personId,
            'project' => (int) $projectId,
        ]);

        return $this->deleteMember($member);
    }

    /**
     * Create a new member relationship for a department, team or perosn
     * @param Request $request
     * @param $projectId
     * @param $type
     * @return View
     */
    protected function postMember(Request $request, $projectId, $type)
    {
        $memberRepositories = [
            'department' => 'DeskPRO:Department',
            'team' => 'DeskPRO:AgentTeam',
            'person' => 'DeskPRO:Person',
        ];

        if (!in_array($type, array_keys($memberRepositories))) {
            throw new \InvalidArgumentException();
        }

        $project = $this->getProject($projectId);

        $submitted = $request->request->all();

        $objectId = $submitted['id'];
        $object = $this->getDoctrine()->getManager()->getRepository($memberRepositories[$type])->find($objectId);

        if (!$object) {
            throw $this->createNotFoundException();
        }

        $member = new ProjectMember();
        $member->setProject($project);
        $setter = 'set' . ucfirst($type);
        $member->$setter($object);

        $validate = [
            'project' => $projectId,
            $type => $objectId,
        ];

        /** @var Form $form */
        $form = $this->get('form.factory')->createNamedBuilder(null, 'projectmember', $member)->getForm();
        $form->submit($validate, true);

        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->persist($member);
            $this->getDoctrine()->getManager()->flush();

            $location = $this->generateUrl('api_project_members_get', array('id' => $member->getId()));

            return View::create(
                $this->dataSerialize($object),
                Response::HTTP_CREATED,
                array(
                    'Location' => $location,
                )
            );
        }

        throw new InvalidFormException($form);
    }

    /**
     * Delete a project member relationship
     * @param $member
     * @return View
     */
    protected function deleteMember($member)
    {
        if (!$member) {
            throw $this->createNotFoundException();
        }

        $this->getDoctrine()->getManager()->remove($member);
        $this->getDoctrine()->getManager()->flush();

        return View::create(
            array(),
            Response::HTTP_OK
        );
    }

    /**
     * Retrieve a single project
     * @param int $id
     * @return Project
     */
    protected function getProject($id)
    {
        $id = (int) $id;
        $project = $this->getDoctrine()->getManager()->getRepository('App:TaskProject')->find($id);

        if (!$project) {
            throw $this->createNotFoundException();
        }

        return $project;
    }

    /**
     * Will be abstracted for use by other controllers
     * @param Request $request
     * @param Project $project
     * @return View
     * @throws WrappedApiErrorException
     */
    protected function handleFormSubmission(Request $request, Project $project)
    {
        $status = $project->getId() ? Response::HTTP_NO_CONTENT : Response::HTTP_CREATED;

        $submitted = $request->request->all();
//        $this->storedMembers = $this->convertMembers($submitted);

        $data = [];
        if (!empty($submitted['title'])) {
            $data = ['title' => $submitted['title']];
        }

        if ($request->getMethod() === 'PUT') {
            $this->oldMembers = $this->convertExistingMembers($project);
        }

        /** @var Form $form */
        $form = $this->get('form.factory')->createNamedBuilder(
            null,
            'project',
            $project,
            ['project' => $project, 'entity_manager' => $this->getDoctrine()->getManager()]
        )->getForm();

        $form->submit($data, $request->getMethod() !== 'PUT');

        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->persist($project);
            $this->getDoctrine()->getManager()->flush();

            $location = $this->generateUrl('api_projects_get', array('id' => $project->getId()));

//            $this->addMembers($project);

            return View::create(
                $this->createRepresentation($project),
                $status,
                array(
                    'Location' => $location,
                )
            );
        }

        throw new InvalidFormException($form);
    }

    /**
     * Take members out for the purposes of form validation
     * @param $request
     * @return array
     */
    private function convertMembers($request)
    {
        $members = array(
            'departments' => array(),
            'teams' => array(),
            'people' => array()
        );

        if (!empty($request['departments'])) {
            $members['departments'] = $request['departments'];
        }
        if (!empty($request['teams'])) {
            $members['teams'] = $request['teams'];
        }
        if (!empty($request['people'])) {
            $members['people'] = $request['people'];
        }

        return $members;
    }

    private function convertExistingMembers(Project $project)
    {
        $members = array(
            'departments' => array(),
            'teams' => array(),
            'people' => array()
        );

        foreach ($project->getMembers() as $member) {
            if (!empty($member->getDepartment())) {
                $id = $member->getId();
                $members['departments'][$id] = $member->getDepartment()->getId();
            } else if (!empty($member->getTeam())) {
                $id = $member->getId();
                $members['teams'][$id] = $member->getTeam()->getId();
            } else if (!empty($member->getPerson())) {
                $id = $member->getId();
                $members['people'][$id] = $member->getPerson()->getId();
            }
        }

        return $members;
    }

    /**
     * Get the appropriate member models and attach them to the new project
     * @param Project $project
     */
    private function addMembers(Project $project)
    {
        if (!empty($this->oldMembers)) {
            $em = $this->getDoctrine()->getManager();

            foreach ($this->oldMembers as $type => $members) {
                $removed = array_diff($members, $this->storedMembers[$type]);
                $newMembers = array_diff($this->storedMembers[$type], $members);

                // Remove the old entities
                foreach ($removed as $id => $member) {
                    $entity = $em->getRepository('App:ProjectMember')->find($id);
                    $em->remove($entity);
                }

                // Commit the new entities
                foreach ($newMembers as $member_id) {
                    $member = new ProjectMember();
                    $member->setProject($project);
                    switch ($type) {
                        case 'departments':
                            $dept = $em->getRepository('DeskPRO:Department')->find($member_id);
                            $member->setDepartment($dept);
                            break;
                        case 'teams':
                            $team = $em->getRepository('DeskPRO:AgentTeam')->find($member_id);
                            $member->setTeam($team);
                            break;
                        case 'people':
                            $person = $em->getRepository('DeskPRO:Person')->find($member_id);
                            $member->setPerson($person);
                            break;
                    }

                    $em->persist($member);
                }
            }

            $em->flush();
        }
    }

    /**
     * Get the Doctrine Query for a project member
     * @param $id
     * @param $object
     * @return \Doctrine\ORM\Query
     */
    protected function getProjectMemberQuery($id, $object)
    {
        /** @var QueryBuilder $queryBuilder */
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->createQueryBuilder()->select('d')->from($object, 'd')
            ->leftJoin('d.project_members', 'p')
            ->where('p.project = :project')
            ->setParameter('project', $id);

        return $queryBuilder->getQuery();
    }
}

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

namespace DeskPRO\Bundle\ApiBundle\Controller\People;

use Application\DeskPRO\Entity\AgentTeam;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Exception\WrappedApiErrorException;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\DataService\AgentTeams\AgentTeamsDataService;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use FOS\RestBundle\Controller\Annotations as Annotations;
use FOS\RestBundle\View\View;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class AgentTeamsController.
 *
 * @ApiModes("all")
 */
class AgentTeamsController extends BaseController
{
    /**
     * Hit this endpoint and you'll fetch list of teams.
     * Provide ids list to fetch only specified teams, or provide
     * "my" filter to fetch teams that authenticated user belongs to.
     *
     * @ApiDoc(
     *     section="Agents",
     *     resourceDescription="Operations about agent`s teams",
     *     description="get a list of teams",
     *     statusCodes={
     *         200="Returned when request was successful"
     *     },
     *     filters={
     *          {"name"="ids", "dataType"="string", "pattern"="1,2,3 ..."},
     *          {"name"="my", "dataType"="boolean", "pattern"="1|0"}
     *     },
     *     output="array<DeskPRO\Bundle\AppBundle\Serializer\Model\AgentTeam>"
     *
     * )
     * @Annotations\Get("/agent_teams", name="api_agent_teams")
     *
     * @param Request $request
     *
     * @return View
     */
    public function cgetAction(Request $request)
    {
        $query = $request->query->all();

        if (!empty($query['ids'])) {
            $teams = $this->selectTeams(explode(',', $query['ids']));
        } else {
            $qb = $this
                ->getDoctrine()
                ->getManager()
                ->createQueryBuilder()
                ->select('t')
                ->from('DeskPRO:AgentTeam', 't')
            ;

            $teams = $qb->getQuery()->getResult();
        }

        /* @var AgentTeam[] $teams */
        if ($request->query->getBoolean('my', false)) {
            $teams = array_filter($teams, function ($team) {
                /** @var AgentTeam $team */
                foreach ($team->getPersonList() as $person) {
                    if ($person->getId() === $this->getUser()->getId()) {
                        return true;
                    }
                }

                return false;
            });
        }

        return View::create($this->wrap($teams));
    }

    /**
     * Get full view of an agent`s team.
     *
     * @ApiDoc(
     *     section="Agents",
     *     resourceDescription="Operations about agent`s teams",
     *     description="get a team",
     *     requirements={
     *         {
     *             "name"="id",
     *             "requirement"="\d+",
     *             "description"="the id of the team",
     *             "dataType"="integer"
     *         }
     *     },
     *     statusCodes={
     *         200="Returned if team was found",
     *         404="Returned if team with specified id was not found"
     *     },
     *     output="DeskPRO\Bundle\AppBundle\Serializer\Model\AgentTeam"
     * )
     * @Annotations\Get("/agent_teams/{id}", name="api_agent_teams_get")
     *
     * @param int $id
     *
     * @return View
     */
    public function getAction($id)
    {
        $team = $this->getAgentTeam($id);

        if (empty($team)) {
            throw $this->createNotFoundException();
        }

        return View::create(
            $this->wrap($team),
            Response::HTTP_OK
        );
    }

    /**
     * Touching this endpoint will return a list of agents belongs to specified team.
     *
     * @ApiDoc(
     *      section="Agents",
     *      resourceDescription="Operations about agent`s teams",
     *      description="Return agents from team given team",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of team",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Will return when success",
     *          404="Returned when chat was not found",
     *          400="In all other cases except system error",
     *      },
     *      output="array<DeskPRO\Bundle\AppBundle\Serializer\Model\ApiPerson>"
     * )
     * @Annotations\Get("/agent_teams/{id}/agents", name="api_agent_teams_agents")
     *
     * @param int $id
     *
     * @return View
     */
    public function getAgentsAction($id)
    {
        /** @var AgentTeamsDataService $service */
        $service = $this->get('data.agent_teams');

        try {
            return View::create(
                $this->wrap($service->getAgentsFromTeam((int) $id)),
                Response::HTTP_OK
            );
        } catch (\InvalidArgumentException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }

    /**
     * This endpoint gives you ability to create an agent team.
     *
     * @ApiDoc(
     *     section="Agents",
     *     resourceDescription="Operations about agent`s team",
     *     description="create a new team",
     *     input={"class"="team", "name"=""},
     *     statusCodes={
     *         201="Will be returned in case of successful team creating",
     *         400="You request was malformed"
     *     },
     *     output="DeskPRO\Bundle\AppBundle\Serializer\Model\AgentTeam"
     * )
     * @Annotations\Post("/agent_teams", name="api_agent_teams_post")
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
        $team = new AgentTeam($this->getUser());

        return $this->handleFormSubmission($request, $team);
    }

    /**
     * This endpoint gives you ability to update an agent team.
     *
     * @APIDoc(
     *     section="Agents",
     *     resourceDescription="Operations about agent`s team",
     *     description="update a team",
     *     requirements={
     *         {
     *             "name"="id",
     *             "requirement"="\d+",
     *             "description"="the id of the team",
     *             "dataType"="integer"
     *         }
     *     },
     *     input={"class"="team", "name"=""},
     *     statusCodes={
     *         204="Returned if team was successfully updated",
     *         400="You request was malformed",
     *         404="Team with specified id was not found"
     *     },
     *     output="DeskPRO\Bundle\AppBundle\Serializer\Model\AgentTeam"
     * )
     * @Annotations\Put("/agent_teams/{id}", name="api_agent_teams_put")
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
        $team = $this->getAgentTeam($id);

        return $this->handleFormSubmission($request, $team);
    }

    /**
     * This endpoint gives you ability to delete an agent team.
     *
     * @APIDoc(
     *     section="Agents",
     *     resourceDescription="Operations about agent`s team",
     *     description="delete a team",
     *     requirements={
     *         {
     *             "name"="id",
     *             "requirement"="\d+",
     *             "description"="the id of the team",
     *             "dataType"="integer"
     *         }
     *     },
     *     statusCodes={
     *         200="Will be returned in case team was succesflly deleted",
     *         404="Not Found"
     *     }
     * )
     * @Annotations\Delete("/agent_teams/{id}", name="api_agent_teams_delete")
     *
     * @param $id
     *
     * @return View
     */
    public function deleteAction($id)
    {
        $team = $this->getAgentTeam($id);
        $this->getDoctrine()->getManager()->remove($team);
        $this->getDoctrine()->getManager()->flush();

        return View::create([]);
    }

    /**
     * @param int $id
     *
     * @return AgentTeam
     */
    protected function getAgentTeam($id)
    {
        $id   = (int) $id;
        $team = $this->getDoctrine()->getManager()->getRepository('DeskPRO:AgentTeam')->find($id);

        if (!$team) {
            throw $this->createNotFoundException();
        }

        return $team;
    }

    /**
     * Will be abstracted for use by other controllers.
     *
     * @param Request   $request
     * @param AgentTeam $team
     *
     * @throws WrappedApiErrorException
     *
     * @return View
     */
    protected function handleFormSubmission(Request $request, AgentTeam $team)
    {
        $status = $team->getId() ? Response::HTTP_NO_CONTENT : Response::HTTP_CREATED;

        /** @var Form $form */
        $form = $this->get('form.factory')->createNamedBuilder(null, 'team', $team)->getForm();

        $submitted = $request->request->all();

        $form->submit($submitted, $request->getMethod() !== 'PUT');

        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->persist($team);
            $this->getDoctrine()->getManager()->flush();

            $location = $this->generateUrl('api_agent_teams_get', ['id' => $team->getId()]);

            return View::create(
                $this->wrap($team),
                $status,
                [
                    'Location' => $location,
                ]
            );
        }

        throw new InvalidFormException($form);
    }

    /**
     * Get specific teams.
     *
     * @param $teamIds
     *
     * @return mixed
     */
    protected function selectTeams($teamIds)
    {
        $em = $this->getDoctrine()->getManager();

        // Clean the IDs
        $teamIds = array_map(function ($value) {
            return (int) $value;
        }, $teamIds);

        $query = $em
            ->createQueryBuilder()
            ->select('t')
            ->from('DeskPRO:AgentTeam', 't')
            ->where('t.id IN (:teamIds)')
            ->setParameter('teamIds', $teamIds)
        ;

        return $query->getQuery();
    }
}

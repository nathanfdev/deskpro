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

namespace DeskPRO\Bundle\ApiBundle\Controller\People;

use Application\DeskPRO\Entity\AgentTeam;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Exception\WrappedApiErrorException;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Error\Exception\InvalidFormException;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AgentTeamsController.
 *
 * @ApiModes("all")
 */
class AgentTeamsController extends BaseController implements ClassResourceInterface
{
    /**
     * @ApiDoc(
     *      description="get a list of teams",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Get("/agent_teams", name="api_agent_teams")
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
            $teams = $this->getDoctrine()->getManager()->createQueryBuilder()
                            ->select('t')->from('DeskPRO:AgentTeam', 't')->getQuery();
        }

        $teams = $teams->getResult();
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

        return View::create(
            $this->dataSerialize($teams),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="get a team",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the team",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      },
     *      output="Application\DeskPRO\Entity\AgentTeam"
     * )
     * @Get("/agent_teams/{id}", name="api_agent_teams_get")
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
            $this->dataSerialize($team),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="create a new team",
     *      input={"class"="team", "name"=""},
     *      statusCodes={
     *          201="Created",
     *          400="Bad Request"
     *      },
     *      output="Application\DeskPRO\Entity\AgentTeam"
     * )
     * @Post("/agent_teams", name="api_agent_teams_post")
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
     * @APIDoc(
     *      description="update a team",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the team",
     *              "dataType"="integer"
     *          }
     *      },
     *      input={"class"="team", "name"=""},
     *      statusCodes={
     *          204="Updated",
     *          400="Bad Request",
     *          404="Not Found"
     *      }
     * )
     * @Put("/agent_teams/{id}", name="api_agent_teams_put")
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
     * @APIDoc(
     *      description="delete a team",
     *      requirements={
     *          {
     *              "name"="id",
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
     * @Delete("/agent_teams/{id}", name="api_agent_teams_delete")
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

        return View::create(
            array(),
            Response::HTTP_OK
        );
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

            $location = $this->generateUrl('api_agent_teams_get', array('id' => $team->getId()));

            return View::create(
                $this->dataSerialize($team),
                $status,
                array(
                    'Location' => $location,
                )
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
        $entityManager = $this->getDoctrine()->getManager();

        // Clean the IDs
        $teamIds = array_map(function ($value) {
            return (int) $value;
        }, $teamIds);

        $query = $entityManager->createQueryBuilder()->select('t')->from('DeskPRO:AgentTeam', 't')
            ->where('t.id IN (:teamIds)')
            ->setParameter('teamIds', $teamIds);

        return $query->getQuery();
    }
}

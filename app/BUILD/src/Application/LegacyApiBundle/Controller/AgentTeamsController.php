<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Person;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use Application\LegacyApiBundle\PermissionStrategy\MultiPermissions;
use Application\LegacyApiBundle\PermissionStrategy\PassPermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Orb\Util\Arrays;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class AgentTeamsController.
 *
 * @ApiModes("all")
 */
class AgentTeamsController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        $multi = new MultiPermissions();
        $multi->addPermissionStrategy(new AdminManagePermission());
        $multi->addPermissionStrategy(new PassPermission(), 'listTeamsAction');

        return $multi;
    }

    //###################################################################################################################
    // list-teams
    //###################################################################################################################

    public function listTeamsAction()
    {
        $data = ['agent_teams' => []];

        foreach ($this->container->getDataService('AgentTeam')->getTeams() as $agent_team) {
            $data['agent_teams'][] = $agent_team->toApiData();
        }

        return $this->createApiResponse($data);
    }

    //###################################################################################################################
    // get-team
    //###################################################################################################################

    public function getTeamAction($id)
    {
        $team = $this->getContainer()->getAgentData()->getTeam($id);

        if (!$team) {
            throw $this->createNotFoundException();
        }

        $data            = $team->toApiData();
        $data['members'] = [];

        foreach ($team->members as $agent) {
            $data['members'][] = $agent->toBasicApiData();
        }

        return $this->createApiResponse(['team' => $data]);
    }

    //###################################################################################################################
    // delete-team
    //###################################################################################################################

    public function deleteTeamAction($id)
    {
        $team = $this->getContainer()->getAgentData()->getTeam($id);

        if (!$team) {
            throw $this->createNotFoundException();
        }

        $old_id = $team->id;
        $this->em->remove($team);
        $this->em->flush();

        return $this->createApiDeleteResponse(
            [
                'old_team_id' => $old_id,
            ]
        );
    }

    //###################################################################################################################
    // save-team
    //###################################################################################################################

    public function saveTeamAction($id)
    {
        if ($id) {
            if (!$team = $this->em->find('DeskPRO:AgentTeam', $id)) {
                throw $this->createNotFoundException();
            }
        } else {
            $team = new AgentTeam();
            $this->em->persist($team);
        }

        $team->name = $this->in->getString('team.name');

        // save avatar
        if ($blobId = $this->in->getUint('team.avatar')) {
            if ($team->avatar && $blobId != $team->avatar['id']) {
                $this->em->remove($team->avatar);
            }
            $blob = $this->em->find('DeskPRO:Blob', $blobId);
            if ($blob && $blob->isImage()) {
                $team->avatar = $blob;
            } else {
                $team->avatar = null;
            }
        } elseif ($team->avatar) {
            $team->avatar && $this->em->remove($team->avatar);
            $team->avatar = null;
        }

        $errors = $this->container->getValidator()->validate($team);
        if (count($errors)) {
            return $this->createApiValidationErrorResponse($errors);
        }

        //------------------------------
        // Save members
        //------------------------------

        $new_members = $this->in->getArrayOfUInts('team.person_ids');
        $new_members = array_unique($new_members);
        $new_members = Arrays::removeFalsey($new_members);

        if ($new_members) {
            $agent_data  = $this->container->getAgentData();
            $new_members = array_filter(
                $new_members,
                function ($a) use ($agent_data) {
                    return $agent_data->get($a) ? true : false;
                }
            );
        }

        $members = $this->em->getRepository('DeskPRO:Person')->findBy(['id' => $new_members]);
        $team->members->clear();
        foreach ($members as $person) {
            /* @var $person Person */
            $person->addTeam($team); // bidirectional
        }

        $is_new = (bool) $team['id'];
        $this->em->flush();

        if ($is_new) {
            return $this->createApiCreateResponse(
                ['team_id' => $team->id],
                $this->generateUrl('api_agent_teams_get', ['id' => $team->id], UrlGeneratorInterface::ABSOLUTE_URL)
            );
        } else {
            return $this->createApiSuccessResponse(['team_id' => $team->id]);
        }
    }
}

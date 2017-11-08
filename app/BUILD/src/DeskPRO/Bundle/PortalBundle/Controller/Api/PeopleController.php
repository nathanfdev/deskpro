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

namespace DeskPRO\Bundle\PortalBundle\Controller\Api;

use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Serializer\Annotation\SerializerView;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PeopleController.
 *
 * @SerializerView(mapping={
 *     "Application\DeskPRO\Entity\Person": "DeskPRO\Bundle\AppBundle\Serializer\Model\Person\WidgetPerson"
 * })
 * @Rest\Route("/portal/api/people")
 */
class PeopleController extends AbstractApiController
{
    /**
     * @Rest\Get("/online_agents")
     *
     * @param Request $request
     *
     * @return View
     */
    public function getOnlineAgentsAction(Request $request)
    {
        $defaultDepartmentId = $request->query->getInt('default_department');
        $agentIds            = $this->getPersonRepository()->getActiveAgentIdsForUserChat();
        $userDepartmentIds   = $this->get('permissions_manager')->getPortalPermissionsBag($this->getUser())->getAllowedChatDepartmentIds();
        $brand               = $this->getBrandContainer()->getBrand();
        $brandDepartmentIds  = $brand->getChatDepartments()->map(function (Department $department) {
            return $department->getId();
        })->getValues();

        $agents = $this->getPersonRepository()->findBy(['id' => $agentIds]);
        $agents = array_filter($agents, function (Person $agent) use ($brandDepartmentIds, $userDepartmentIds, $defaultDepartmentId) {
            $agent->loadHelper('AgentPermissions');
            $agentDepartmentIds = $agent->getHelper('AgentPermissions')->getAllowedDepartments('chat');

            if (!$agent->hasPerm('agent_chat.use')) {
                return false;
            }

            $allowedDepartmentIds = array_intersect($brandDepartmentIds, $agentDepartmentIds, $userDepartmentIds);
            if (!$allowedDepartmentIds) {
                return false;
            }

            if ($defaultDepartmentId && !in_array($defaultDepartmentId, $allowedDepartmentIds)) {
                return false;
            }

            return true;
        });

        return new View($this->wrap($agents));
    }

    /**
     * @Rest\Get("")
     *
     * @param Request $request
     *
     * @return View
     */
    public function getPeopleAction(Request $request)
    {
        // allow to fetch only people who is in person's chat
        $allowedIds = [];
        $lastChat   = $this->getLastChat();

        if ($this->getUser() && $this->getUser()->getId()) {
            $allowedIds[] = $this->getUser()->getId();
        }
        if ($lastChat) {
            if ($lastChat->getAgent()) {
                $allowedIds[] = $lastChat->getAgent()->getId();
            }

            /** @var \Application\DeskPRO\DBAL\Connection $connection */
            $connection       = $this->getManager()->getConnection();
            $messagePeopleIds = $connection->fetchAllCol(
                'SELECT DISTINCT author_id FROM chat_messages WHERE conversation_id = :last_chat_id',
                [
                    'last_chat_id' => $lastChat->getId(),
                ]
            );

            $messagePeopleIds = array_map('intval', $messagePeopleIds);
            foreach ($messagePeopleIds as $id) {
                if ($id) {
                    $allowedIds[] = $id;
                }
            }
        }

        $fetchIds = (array) $request->get('ids');
        $fetchIds = array_map('intval', $fetchIds);
        $fetchIds = array_intersect($fetchIds, $allowedIds);

        $people = $this->getPersonRepository()->findBy(['id' => $fetchIds]);

        return new View($this->wrap($people));
    }
}

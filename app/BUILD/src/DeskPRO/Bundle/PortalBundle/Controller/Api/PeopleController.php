<?php

namespace DeskPRO\Bundle\PortalBundle\Controller\Api;

use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\UserChatQueue;
use DeskPRO\Bundle\AppBundle\Serializer\Annotation\SerializerView;
use DeskPRO\Bundle\VoiceBundle\UserChat\UserChatQueueTargetsChecker;
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
        $defaultQueueId      = $this->get('chat_settings_resolver')->getDefaultQueue();
        $agentIds            = $this->getPersonRepository()->getActiveAgentIdsForUserChat();
        $userDepartmentIds   = $this->get('permissions_manager')->getPortalPermissionsBag($this->getUser())->getAllowedChatDepartmentIds();
        $brand               = $this->getBrandContainer()->getBrand();
        $brandDepartmentIds  = $brand->getChatDepartments()->map(function (Department $department) {
            return $department->getId();
        })->getValues();

        /** @var UserChatQueue[] $chatQueues */
        $chatQueues = $this->getManager()->getRepository(UserChatQueue::class)->findAll();
        /** @var Department[] $chatDepartments */
        $chatDepartments = $this->getManager()->getRepository(Department::class)->findBy([
            'is_chat_enabled' => true,
        ]);

        $queueTargetsChecker = new UserChatQueueTargetsChecker();
        $defaultChatQueues   = array_filter($chatQueues, function (UserChatQueue $chatQueue) use ($defaultQueueId) {
            return $defaultQueueId && $chatQueue->getId() === (int) $defaultQueueId;
        });

        $defaultChatQueue = reset($defaultChatQueues);
        if (!$defaultChatQueue && count($chatQueues) > 0) {
            $defaultChatQueue = $chatQueues[0];
        }

        $agents = $this->getPersonRepository()->findBy(['id' => $agentIds]);
        $agents = array_filter($agents, function (Person $agent) use (
            $brandDepartmentIds,
            $userDepartmentIds,
            $defaultDepartmentId,
            $chatDepartments,
            $defaultChatQueue,
            $queueTargetsChecker
        ) {
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

            $agentDepartments = array_filter($chatDepartments, function (Department $department) use ($allowedDepartmentIds) {
                return in_array($department->getId(), $allowedDepartmentIds);
            });

            $hasQueueDepartment = false;
            foreach ($agentDepartments as $department) {
                $chatQueue = $department->getChatQueue();
                if (!$chatQueue) {
                    $chatQueue = $defaultChatQueue;
                }
                if ($queueTargetsChecker->isAgentMemberOfChatQueue($chatQueue, $agent)) {
                    $hasQueueDepartment = true;
                    break;
                }
            }

            if (!$hasQueueDepartment) {
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

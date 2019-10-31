<?php

namespace DeskPRO\Bundle\PortalBundle\Controller\Api;

use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\UserChatQueue;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\VoiceBundle\UserChat\UserChatQueueTargetsChecker;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ChatController.
 *
 * @Rest\Route("/portal/api/chat_departments")
 */
class ChatDepartmentsController extends AbstractApiController
{
    /**
     * @Rest\Get("")
     *
     * @param Request $request
     *
     * @return ApiWrapper
     */
    public function getChatDepartmentsAction(Request $request)
    {
        $permissionBag        = $this->get('permissions_manager')->getPortalPermissionsBag($this->getUser());
        $allowedDepartmentIds = $permissionBag->getAllowedChatDepartmentIds();

        // exclude offline departments
        if ($request->query->get('online')) {
            // get all available departments ids of all active agents
            $agentIds = $this->getPersonRepository()->getActiveAgentIdsForUserChat();
            $agents   = $this->getPersonRepository()->findBy(['id' => $agentIds]);

            /** @var Department[] $chatDepartments */
            $chatDepartments = $this->getManager()->getRepository(Department::class)->findBy([
                'is_chat_enabled' => true,
            ]);

            /** @var UserChatQueue[] $chatQueues */
            $chatQueues     = $this->getManager()->getRepository(UserChatQueue::class)->findAll();
            $defaultQueueId = $this->get('chat_settings_resolver')->getDefaultQueue();

            $queueTargetsChecker = new UserChatQueueTargetsChecker();
            $defaultChatQueues   = array_filter($chatQueues, function (UserChatQueue $chatQueue) use ($defaultQueueId) {
                return $defaultQueueId && $chatQueue->getId() === (int) $defaultQueueId;
            });

            $defaultChatQueue = reset($defaultChatQueues);
            if (!$defaultChatQueue && count($chatQueues) > 0) {
                $defaultChatQueue = $chatQueues[0];
            }

            $allAgentDepartmentIds = [];

            /** @var Person $agent */
            foreach ($agents as $agent) {
                $agent->loadHelper('AgentPermissions');
                $agentDepartmentIds = $agent->getHelper('AgentPermissions')->getAllowedDepartments('chat');
                $agentDepartments   = array_filter($chatDepartments, function (Department $department) use ($agentDepartmentIds) {
                    return in_array($department->getId(), $agentDepartmentIds);
                });

                foreach ($agentDepartments as $department) {
                    $chatQueue = $department->getChatQueue();
                    if (!$chatQueue) {
                        $chatQueue = $defaultChatQueue;
                    }
                    if ($queueTargetsChecker->isAgentMemberOfChatQueue($chatQueue, $agent)) {
                        $allAgentDepartmentIds[] = $department->getId();
                    }
                }
            }

            $allowedDepartmentIds = array_intersect($allowedDepartmentIds, $allAgentDepartmentIds);
        }

        $qb = $this->getManager()->createQueryBuilder();
        $qb
            ->select('d')
            ->from(Department::class, 'd')
            ->join('d.brands', 'b')
            ->where(
                'd.is_chat_enabled = true',
                'd.id IN (:allowed_department_ids)',
                'b.id IN(:brand)'
            )
            ->setParameter('allowed_department_ids', $allowedDepartmentIds)
            ->setParameter('brand', $this->get('brand_stack')->getActive()->getBrand())
        ;

        return $this->wrap($qb->getQuery()->getResult());
    }
}

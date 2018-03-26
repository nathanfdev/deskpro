<?php

namespace DeskPRO\Bundle\PortalBundle\Controller\Api;

use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
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
        $agentDepartmentIds   = [];

        // exclude offline departments
        if ($request->query->get('online')) {
            // get all available departments ids of all active agents
            $agentIds = $this->getPersonRepository()->getActiveAgentIdsForUserChat();
            $agents   = $this->getPersonRepository()->findBy(['id' => $agentIds]);

            /** @var Person $agent */
            foreach ($agents as $agent) {
                $agent->loadHelper('AgentPermissions');
                $agentDepartmentIds = array_merge(
                    $agentDepartmentIds,
                    $agent->getHelper('AgentPermissions')->getAllowedDepartments('chat')
                );
            }

            $allowedDepartmentIds = array_intersect($allowedDepartmentIds, $agentDepartmentIds);
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

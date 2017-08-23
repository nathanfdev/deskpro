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

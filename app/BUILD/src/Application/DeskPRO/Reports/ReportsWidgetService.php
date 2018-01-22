<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace Application\DeskPRO\Reports;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\ReportWidget;
use Application\DeskPRO\EntityRepository\AgentTeam as AgentTeamRepository;
use Application\DeskPRO\EntityRepository\Department as DepartmentRepository;
use Application\DeskPRO\EntityRepository\Person as PersonRepository;
use Application\DeskPRO\EntityRepository\ReportWidget as ReportWidgetRepository;
use Doctrine\ORM\EntityManager;

class ReportsWidgetService
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var ReportWidgetRepository
     */
    private $repository;

    /**
     * ReportsWidgetService constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em         = $em;
        $this->repository = $this->em->getRepository(ReportWidget::class);
    }

    /**
     * @return array
     */
    public function getGroupParams()
    {
        $groupParams = $this->repository->getReportGroupParams();

        $groupParams['values'] = [
            'agent'      => [],
            'department' => [],
            'team'       => [],
        ];

        /** @var PersonRepository $personRepository */
        $personRepository = $this->em->getRepository(Person::class);
        $agents           = $personRepository->getAgents();

        /** @var DepartmentRepository $departmentRepository */
        $departmentRepository = $this->em->getRepository(Department::class);
        $departments          = $departmentRepository->getAll();

        /** @var AgentTeamRepository $agentTeamRepository */
        $agentTeamRepository = $this->em->getRepository(AgentTeam::class);
        $agentTeams          = $agentTeamRepository->getTeams();

        foreach ($agents as $agent) {
            $groupParams['values']['agent'][$agent->getId()] = [$agent->getDisplayName()];
        }

        foreach ($departments as $department) {
            $postfix                                                   = $department->isTicketsEnabled() ? '' : ' [Chat]';
            $groupParams['values']['department'][$department->getId()] = [$department->getTitle().$postfix];
        }

        foreach ($agentTeams as $agentTeam) {
            $groupParams['values']['team'][$agentTeam->getId()] = [$agentTeam->getName()];
        }

        return $groupParams;
    }
}

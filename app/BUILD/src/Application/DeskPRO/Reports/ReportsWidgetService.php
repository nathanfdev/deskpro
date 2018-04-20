<?php

namespace Application\DeskPRO\Reports;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\CustomDefArticle;
use Application\DeskPRO\Entity\CustomDefBilling;
use Application\DeskPRO\Entity\CustomDefChat;
use Application\DeskPRO\Entity\CustomDefFeedback;
use Application\DeskPRO\Entity\CustomDefOrganization;
use Application\DeskPRO\Entity\CustomDefPerson;
use Application\DeskPRO\Entity\CustomDefProduct;
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\ReportWidget;
use Application\DeskPRO\EntityRepository\AgentTeam as AgentTeamRepository;
use Application\DeskPRO\EntityRepository\Department as DepartmentRepository;
use Application\DeskPRO\EntityRepository\Organization as OrganizationRepository;
use Application\DeskPRO\EntityRepository\Person as PersonRepository;
use Application\DeskPRO\EntityRepository\ReportWidget as ReportWidgetRepository;
use DeskPRO\Bundle\ReportBundle\Dashboard\DashboardWidgetManager;
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
            'agent'        => [],
            'department'   => [],
            'team'         => [],
            'organization' => [],
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

        /** @var OrganizationRepository $orgRepository */
        $orgRepository = $this->em->getRepository(Organization::class);

        $groupParams['values']['agent'][DashboardWidgetManager::WIDGET_VALUE_FROM_REPORT] = ['value from report'];
        foreach ($agents as $agent) {
            $groupParams['values']['agent'][$agent->getId()] = [$agent->getDisplayName()];
        }

        $groupParams['values']['department'][DashboardWidgetManager::WIDGET_VALUE_FROM_REPORT] = ['value from report'];
        foreach ($departments as $department) {
            $postfix                                                   = $department->isTicketsEnabled() ? '' : ' [Chat]';
            $groupParams['values']['department'][$department->getId()] = [$department->getTitle().$postfix];
        }

        $groupParams['values']['team'][DashboardWidgetManager::WIDGET_VALUE_FROM_REPORT] = ['value from report'];
        foreach ($agentTeams as $agentTeam) {
            $groupParams['values']['team'][$agentTeam->getId()] = [$agentTeam->getName()];
        }

        $groupParams['values']['organization'] = array_map(
            function ($org) {
                return [$org];
            },
            $orgRepository->getOrganizationNames()
        );

        $groupParams['values']['organization'][DashboardWidgetManager::WIDGET_VALUE_FROM_REPORT] = ['value from report'];

        $groupParams['ticket_custom_fields']   = $this->customFields(CustomDefTicket::class);
        $groupParams['org_custom_fields']      = $this->customFields(CustomDefOrganization::class);
        $groupParams['user_custom_fields']     = $this->customFields(CustomDefPerson::class);
        $groupParams['article_custom_fields']  = $this->customFields(CustomDefArticle::class);
        $groupParams['chat_custom_fields']     = $this->customFields(CustomDefChat::class);
        $groupParams['feedback_custom_fields'] = $this->customFields(CustomDefFeedback::class);
        $groupParams['billing_custom_fields']  = $this->customFields(CustomDefBilling::class);
        $groupParams['product_custom_fields']  = $this->customFields(CustomDefProduct::class);

        return $groupParams;
    }

    /**
     * @param $class
     *
     * @return array
     */
    private function customFields($class)
    {
        $defs   = $this->em->getRepository($class)->findBy(['parent' => null, 'is_enabled' => true]);
        $result = [];

        foreach ($defs as $def) {
            /** @var CustomDefAbstract $def */
            if ($def->isChoiceType()) {
                $choices = $def->getChoices();
                $arr     = [];

                $maxLevel = null;
                if ($def->getOption('reports_field_var_max_level')) {
                    $maxLevel = (int) $def->getOption('reports_field_var_max_level');
                }
                $this->getChoices($choices, $def->getTitle(), $arr, $maxLevel);
                $result[$def->getRawTitle()][$def->getTitle()] = [$def->getTitle()];
                $result[$def->getRawTitle()] += $arr;
                $result[$def->getRawTitle()][DashboardWidgetManager::WIDGET_VALUE_FROM_REPORT] = ['value from report'];
            }
        }

        return $result;
    }

    /**
     * @param array  $choices
     * @param string $prevTitle
     * @param array  $result
     * @param int    $maxLevel
     * @param int    $level
     */
    private function getChoices(array $choices, $prevTitle, array &$result, $maxLevel = null, $level = 1)
    {
        /* @var CustomDefAbstract $def */
        foreach ($choices as $choice) {
            $title                    = $prevTitle ? $prevTitle.' > ' : '';
            $result[$choice['title']] = [$title.$choice['title']];
            if ((!$maxLevel || $level < $maxLevel) && isset($choice['children'])) {
                $this->getChoices($choice['children'], $title.$choice['title'], $result, ++$level);
            }
        }
    }
}

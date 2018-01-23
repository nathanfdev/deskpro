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

/**
 * DeskPRO.
 *
 * @category Install
 */

namespace Application\InstallBundle\Data\DefaultData;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\ReportDashboard as Dashboard;
use Application\DeskPRO\Entity\ReportDashboardPermission as Permission;
use Application\DeskPRO\Entity\ReportDashboardReport as Tab;
use Application\DeskPRO\Entity\ReportDashboardWidget as Widget;
use Application\DeskPRO\Entity\ReportWidget;
use Application\DeskPRO\Entity\ReportWidget as WidgetPrototype;
use Application\DeskPRO\EntityRepository\Person as PersonRepository;

class DashboardData extends AbstractDefaultData
{
    const PRIORITY = 20000;

    protected $dashboards = [
        [
            'title'   => 'Ticket insights',
            'reports' => [
                [
                    'title'      => 'Overview',
                    'sort_order' => 1,
                    'widgets'    => [
                        [
                            'title'      => 'Backlog',
                            'position'   => '0:0',
                            'size'       => '3:3',
                            'widget_key' => 'tickets-awaiting-agent',
                            'type'       => 'simple_stat',
                        ],
                        [
                            'title'      => 'Online agents',
                            'position'   => '0:3',
                            'size'       => '3:3',
                            'widget_key' => 'agents-online',
                            'type'       => 'simple_stat',
                        ],
                        [
                            'title'      => 'New tickets today',
                            'position'   => '0:7',
                            'size'       => '3:3',
                            'widget_key' => 'tickets-created-x-date',
                            'type'       => 'simple_stat',
                            'variables'  => [
                                [
                                    'name'  => 'date',
                                    'type'  => 'dates',
                                    'value' => 'today',
                                ],
                            ],
                        ],
                        [
                            'title'      => 'New chats today',
                            'position'   => '0:10',
                            'size'       => '3:3',
                            'widget_key' => 'chats-created-x-date',
                            'type'       => 'simple_stat',
                            'variables'  => [
                                [
                                    'name'  => 'date',
                                    'type'  => 'dates',
                                    'value' => 'today',
                                ],
                            ],
                        ],
                        [
                            'title'      => 'Avg response time',
                            'position'   => '0:14',
                            'size'       => '3:3',
                            'widget_key' => 'avg-response-time-x-date',
                            'type'       => 'simple_stat',
                            'variables'  => [
                                [
                                    'name'  => 'date',
                                    'type'  => 'dates',
                                    'value' => 'today',
                                ],
                            ],
                        ],
                        [
                            'title'      => 'Satisfaction today',
                            'position'   => '0:17',
                            'size'       => '3:3',
                            'widget_key' => 'satisfaction-x-date',
                            'type'       => 'simple_stat',
                            'variables'  => [
                                [
                                    'name'  => 'date',
                                    'type'  => 'dates',
                                    'value' => 'today',
                                ],
                            ],
                        ],
                        [
                            'title'      => 'Replies today',
                            'position'   => '0:20',
                            'size'       => '3:3',
                            'widget_key' => 'replies-created-x-date',
                            'type'       => 'simple_stat',
                            'variables'  => [
                                [
                                    'name'  => 'date',
                                    'type'  => 'dates',
                                    'value' => 'today',
                                ],
                            ],
                        ],
                        [
                            'title'      => 'Resolved today',
                            'position'   => '0:23',
                            'size'       => '3:3',
                            'widget_key' => 'tickets-resolved-x-date',
                            'type'       => 'simple_stat',
                            'variables'  => [
                                [
                                    'name'  => 'date',
                                    'type'  => 'dates',
                                    'value' => 'today',
                                ],
                            ],
                        ],
                        // 2-nd row
                        [
                            'title'      => 'Backlog by Department',
                            'position'   => '3:0',
                            'size'       => '6:6',
                            'widget_key' => 'number-tickets-status-grouped-by-x-y',
                            'type'       => 'simple_bars',
                            'variables'  => [
                                0 => [
                                        'name'       => 'ticket',
                                        'type'       => 'fields',
                                        'field_type' => 'tickets',
                                        'table'      => 'tickets',
                                        'default'    => 'department',
                                        'value'      => 'department',
                                    ],
                                1 => [
                                        'name'       => 'ticket_2',
                                        'type'       => 'fields',
                                        'field_type' => 'tickets',
                                        'table'      => 'tickets',
                                        'default'    => 'agent',
                                        'value'      => 'none',
                                    ],
                                2 => [
                                        'name'       => 'status',
                                        'type'       => 'statuses',
                                        'field_type' => 'tickets',
                                        'table'      => 'tickets',
                                        'default'    => 'awaiting_agent',
                                        'value'      => 'awaiting_agent',
                                    ],
                            ],
                        ],
                        [
                            'title'      => 'Backlog by Team',
                            'position'   => '3:7',
                            'size'       => '6:6',
                            'widget_key' => 'number-tickets-status-grouped-by-x-y',
                            'type'       => 'simple_bars',
                            'options'    => '{"legend": false}',
                            'variables'  => [

                                [
                                    'name'       => 'ticket',
                                    'type'       => 'fields',
                                    'field_type' => 'tickets',
                                    'table'      => 'tickets',
                                    'default'    => 'department',
                                    'value'      => 'agent_team',
                                ],
                                [
                                    'name'       => 'ticket_2',
                                    'type'       => 'fields',
                                    'field_type' => 'tickets',
                                    'table'      => 'tickets',
                                    'default'    => 'agent',
                                    'value'      => 'none',
                                ],
                                [
                                    'name'       => 'status',
                                    'type'       => 'statuses',
                                    'field_type' => 'tickets',
                                    'table'      => 'tickets',
                                    'default'    => 'awaiting_agent',
                                    'value'      => 'awaiting_agent',
                                ],
                            ],
                        ],
                        [
                            'title'      => 'Daily activity',
                            'position'   => '3:14',
                            'size'       => '12:6',
                            'widget_key' => 'daily-activity',
                            'type'       => 'simple_bars',
                            'variables'  => [
                                [
                                    'name'  => 'date',
                                    'type'  => 'dates',
                                    'value' => 'today',
                                ],
                            ],
                        ],
                        // 3rd row
                        [
                            'title'      => 'Top agents',
                            'position'   => '9:0',
                            'size'       => '6:12',
                            'widget_key' => 'number-of-replies-created-x-date-grouped-by-agent',
                            'type'       => 'table',
                            'variables'  => [
                                [
                                    'name'  => 'date',
                                    'type'  => 'dates',
                                    'value' => 'today',
                                ],
                            ],
                        ],
                        [
                            'title'      => 'SLA Status',
                            'position'   => '9:7',
                            'size'       => '6:6',
                            'widget_key' => 'incomplete-sla',
                            'type'       => 'pie',
                            'variables'  => [],
                        ],
                        [
                            'title'      => 'New Tickets by Channel',
                            'position'   => '9:14',
                            'size'       => '6:6',
                            'widget_key' => 'tickets-by-channel-created-x-date',
                            'type'       => 'pie',
                            'variables'  => [
                                [
                                    'name'  => 'date',
                                    'type'  => 'dates',
                                    'value' => 'today',
                                ],
                            ],
                            'options' => '{"legend":false,"labelsEnabled":false}',
                        ],
                        [
                            'title'      => 'New Tickets by Department',
                            'position'   => '9:20',
                            'size'       => '6:6',
                            'widget_key' => 'number-tickets-created-date-grouped-by-x-y',
                            'type'       => 'pie',
                            'variables'  => [

                                [
                                    'name'       => 'ticket',
                                    'type'       => 'fields',
                                    'field_type' => 'tickets',
                                    'table'      => 'tickets',
                                    'default'    => 'department',
                                    'value'      => 'department',
                                ],
                                [
                                    'name'       => 'ticket_2',
                                    'type'       => 'fields',
                                    'field_type' => 'tickets',
                                    'table'      => 'tickets',
                                    'default'    => 'agent',
                                    'value'      => 'none',
                                ],
                                [
                                    'name'    => 'date',
                                    'type'    => 'dates',
                                    'default' => 'today',
                                    'value'   => 'today',
                                ],
                            ],
                            'options' => '{"legend":false,"labelsEnabled":false}',
                        ],
                        // 4th row
                        [
                            'title'      => 'Time to first reply',
                            'position'   => '15:7',
                            'size'       => '6:6',
                            'widget_key' => 'tickets-replied-x-date-grouped-by-first-reply',
                            'type'       => 'pie',
                            'variables'  => [
                                [
                                    'name'    => 'date',
                                    'type'    => 'dates',
                                    'default' => 'today',
                                    'value'   => 'today',
                                ],
                            ],
                            'options' => '{"legend":false,"labelsEnabled":false}',
                        ],
                        // 5th row
                        [
                            'title'      => 'KB views',
                            'position'   => '21:0',
                            'size'       => '6:6',
                            'widget_key' => 'kb-views-x-date',
                            'type'       => 'table',
                            'variables'  => [
                                [
                                    'name'    => 'date',
                                    'type'    => 'dates',
                                    'default' => 'today',
                                    'value'   => 'today',
                                ],
                            ],
                            'options' => '{"legend":false,"labelsEnabled":false}',
                        ],
                        [
                            'title'      => 'Top KB searches',
                            'position'   => '21:7',
                            'size'       => '6:6',
                            'widget_key' => 'kb-searches-x-date',
                            'type'       => 'table',
                            'variables'  => [
                                [
                                    'name'    => 'date',
                                    'type'    => 'dates',
                                    'default' => 'today',
                                    'value'   => 'today',
                                ],
                            ],
                        ],
                        [
                            'title'      => 'Chats by agent',
                            'position'   => '21:14',
                            'size'       => '6:6',
                            'widget_key' => 'number-chats-created-date-grouped-by-x',
                            'type'       => 'simple_bars',
                            'variables'  => [
                                [
                                    'name'    => 'date',
                                    'type'    => 'dates',
                                    'default' => 'today',
                                    'value'   => 'today',
                                ],
                                [
                                    'name'       => 'chat',
                                    'type'       => 'fields',
                                    'field_type' => 'chats',
                                    'table'      => 'chat_conversations',
                                    'default'    => 'agent',
                                    'value'      => 'agent',
                                ],
                            ],
                            'options' => '{"legend": false}',
                        ],
                        [
                            'title'      => 'Top snippets',
                            'position'   => '21:20',
                            'size'       => '6:6',
                            'widget_key' => 'top-snippets-x-date',
                            'type'       => 'table',
                            'variables'  => [
                                [
                                    'name'    => 'date',
                                    'type'    => 'dates',
                                    'default' => 'today',
                                    'value'   => 'today',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        [
            'title'   => 'Chat insights',
            'reports' => [
                [
                    'title'      => 'Overview',
                    'sort_order' => 1,
                ],
            ],
        ],
    ];

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function runInstall()
    {
        /** @var PersonRepository $personRepository */
        $personRepository = $this->getEm()->getRepository(Person::class);

        /** @var Person[] $admins */
        $admins = $personRepository->findBy(['can_admin' => 1]);
        /** @var Person[] $agents */
        $agents = $personRepository->findBy(['is_agent' => 1]);

        foreach ($this->dashboards as $dashboard) {
            $dashboardEntity = new Dashboard();
            $dashboardEntity->setTitle($dashboard['title'])->setIsDefault(true);
            $this->syncDashboard($dashboardEntity, $dashboard);
            $this->getEm()->persist($dashboardEntity);
            foreach ($admins as $admin) {
                $permissions = new Permission();
                $permissions->setDashboard($dashboardEntity)->setPerson($admin)->setName(Permission::FULL);
                $this->getEm()->persist($permissions);
            }
            foreach ($agents as $agent) {
                if (!$agent->isAdmin()) {
                    $permissions = new Permission();
                    $permissions
                        ->setName(Permission::VIEW)
                        ->setDashboard($dashboardEntity)
                        ->setPerson($agent);
                    $this->getEm()->persist($permissions);
                }
            }
            $this->getEm()->flush();
        }
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function runReset()
    {
        $dashboards = $this->getEm()->getRepository(Dashboard::class)->findBy(['is_default' => 1]);
        foreach ($dashboards as $dashboard) {
            $this->getEm()->remove($dashboard);
        }
        $this->getEm()->flush();
    }

    /**
     * @param Dashboard $dashboardEntity
     * @param array     $dashboard
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    protected function syncDashboard(Dashboard $dashboardEntity, array $dashboard)
    {
        if ($dashboardEntity->getId() > 0) {
            foreach ($dashboardEntity->getReports() as $report) {
                $this->getEm()->remove($report);
            }
            $this->getEm()->flush();
        }
        $widgetRepository = $this->getEm()->getRepository(ReportWidget::class);
        foreach ($dashboard['reports'] as $report) {
            $tab = new Tab();
            $tab
                ->setTitle($report['title'])
                ->setDashboard($dashboardEntity)
                ->setSortOrder($report['sort_order']);
            $this->getEm()->persist($tab);
            if (isset($report['widgets'])) {
                foreach ($report['widgets'] as $widget) {
                    $widgetEntity = new Widget();
                    $widgetEntity
                        ->setTitle($widget['title'])
                        ->setSize($widget['size'])
                        ->setPosition($widget['position'])
                        ->setReport($tab);
                    if (isset($widget['widget_id'])) {
                        /** @var WidgetPrototype $widgetPrototype */
                        $widgetPrototype = $widgetRepository->find($widget['widget_id']);
                        $widgetEntity->setWidget($widgetPrototype);
                    }
                    if (isset($widget['widget_key'])) {
                        $widgetPrototype = $widgetRepository->findOneBy(['unique_key' => $widget['widget_key']]);
                        $widgetEntity->setWidget($widgetPrototype);
                    }
                    $widgetEntity->setType($widget['type']);
                    if (isset($widget['variables'])) {
                        $widgetEntity->setVariables($widget['variables']);
                    }
                    if (isset($widget['options'])) {
                        $widgetEntity->setOptions($widget['options']);
                    }

                    $this->getEm()->persist($widgetEntity);
                }
            }
        }
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function runSync()
    {
        $dashboards = $this->getEm()->getRepository(Dashboard::class)->findBy(['is_default' => 1]);
        foreach ($dashboards as $dashboard) {
            foreach ($this->dashboards as $dashboardData) {
                if ($dashboard->getTitle() === $dashboardData['title']) {
                    $this->syncDashboard($dashboard, $dashboardData);
                    $this->getEm()->persist($dashboard);
                }
            }
        }
        $this->getEm()->flush();
    }
}

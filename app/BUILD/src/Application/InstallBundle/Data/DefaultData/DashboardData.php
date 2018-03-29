<?php

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
use DeskPRO\Component\Util\MapUtils;

class DashboardData extends AbstractDefaultData
{
    const PRIORITY  = 20000;
    const UNIT_SIZE = 4; // 1 unit = 2 blocks. ie 2:2 is the same as 6:6

    protected $dashboards = [
        [
            'title'         => 'Ticket Insights',
            'system_name'   => 'ticket_insights',
            'display_order' => -1000,
            'reports'       => [
                [
                    'title'      => 'Overview',
                    'sort_order' => 1,
                    'widgets'    => [
                        [
                            'title'      => 'Backlog',
                            'position'   => '0:0',
                            'size'       => '1:1',
                            'widget_key' => 'tickets-awaiting-agent',
                            'type'       => 'simple_stat',
                        ],
                        [
                            'title'      => 'Online Agents',
                            'position'   => '0:1',
                            'size'       => '1:1',
                            'widget_key' => 'agents-online',
                            'type'       => 'simple_stat',
                        ],
                        [
                            'title'      => 'New Tickets Today',
                            'position'   => '0:2',
                            'size'       => '1:1',
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
                            'title'      => 'New Chats Today',
                            'position'   => '0:3',
                            'size'       => '1:1',
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
                            'title'      => 'Avg Response Time',
                            'position'   => '0:4',
                            'size'       => '1:1',
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
                            'title'      => 'Satisfaction Today',
                            'position'   => '0:5',
                            'size'       => '1:1',
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
                            'title'      => 'Replies Today',
                            'position'   => '0:6',
                            'size'       => '1:1',
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
                            'title'      => 'Resolved Today',
                            'position'   => '0:7',
                            'size'       => '1:1',
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
                            'position'   => '1:0',
                            'size'       => '2:2',
                            'widget_key' => 'number-tickets-status-grouped-by-x-y',
                            'type'       => 'simple_bars',
                            'options'    => '{"legend":false,"categoryAxis":{"title":null},"valueAxes":[{"stackType": "regular", "title": null}],"categoryAxis":{"labelRotation": 45}}',
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
                                        'value'      => 'department',
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
                            'title'      => 'Backlog by Team',
                            'position'   => '1:2',
                            'size'       => '2:2',
                            'widget_key' => 'number-tickets-status-grouped-by-x-y',
                            'type'       => 'simple_bars',
                            'options'    => '{"legend":false,"valueAxes":[{"stackType": "regular", "title": null}],"categoryAxis":{"title":null}}',
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
                            'title'      => 'Replies Today',
                            'position'   => '1:4',
                            'size'       => '4:2',
                            'widget_key' => 'daily-activity',
                            'type'       => 'simple_bars',
                            'variables'  => [
                                [
                                    'name'  => 'date',
                                    'type'  => 'dates',
                                    'value' => 'yesterday',
                                ],
                            ],
                            'options' => '{"legend":false}',
                        ],
                        // 3rd row
                        [
                            'title'      => 'Top Agents',
                            'position'   => '3:0',
                            'size'       => '2:4',
                            'widget_key' => 'number-of-replies-created-x-date-grouped-by-agent',
                            'type'       => 'table',
                            'options'    => '{"noGroupingColumn":true}',
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
                            'position'   => '3:2',
                            'size'       => '2:2',
                            'widget_key' => 'incomplete-sla',
                            'type'       => 'pie',
                            'variables'  => [],
                            'options'    => '{"legend":false,"labelsEnabled":false}',
                        ],
                        [
                            'title'      => 'New Tickets by Channel',
                            'position'   => '3:4',
                            'size'       => '2:2',
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
                            'position'   => '3:6',
                            'size'       => '2:2',
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
                                    'value'      => 'department',
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
                            'position'   => '5:2',
                            'size'       => '2:2',
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
                        [
                            'title'      => 'New Tickets by Hour',
                            'position'   => '5:4',
                            'size'       => '4:2',
                            'widget_key' => 'tickets-opened-within-x-date-grouped-by-hour',
                            'type'       => 'simple_lines',
                            'variables'  => [
                                [
                                    'name'    => 'date',
                                    'type'    => 'dates',
                                    'default' => 'past_24_hours',
                                    'value'   => 'past_24_hours',
                                ],
                            ],
                            'options' => '{"legend":false}',
                        ],
                        // 5th row
                        [
                            'title'      => 'Top KB Views',
                            'position'   => '7:0',
                            'size'       => '2:2',
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
                            'options' => '{"noGroupingColumn":true}',
                        ],
                        [
                            'title'      => 'Top KB Searches',
                            'position'   => '7:2',
                            'size'       => '2:2',
                            'widget_key' => 'kb-searches-x-date',
                            'type'       => 'table',
                            'options'    => '{"noGroupingColumn":true}',
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
                            'title'      => 'Chats by Agent',
                            'position'   => '7:4',
                            'size'       => '2:2',
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
                            'title'      => 'Top Snippets',
                            'position'   => '7:6',
                            'size'       => '2:2',
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
            'title'         => 'Chat Insights',
            'system_name'   => 'chat_insights',
            'display_order' => -900,
            'reports'       => [
                [
                    'title'      => 'Overview',
                    'sort_order' => 2,
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

        $dashboardEnts = $this->getEm()->getRepository(Dashboard::class)->findBy(['is_default' => 1]);
        $dashboardEnts = MapUtils::rekeyByGetter($dashboardEnts, 'getSystemName');

        foreach ($this->dashboards as $dashboard) {
            if (isset($dashboardEnts[$dashboard['system_name']])) {
                $dashboardEntity = $dashboardEnts[$dashboard['system_name']];
            } else {
                $dashboardEntity = new Dashboard();
            }

            $dashboardEntity
                ->setTitle($dashboard['title'])
                ->setIsDefault(true)
                ->setSystemName($dashboard['system_name'])
            ;
            $this->syncDashboard($dashboardEntity, $dashboard);
            $this->getEm()->persist($dashboardEntity);
            if (!$dashboardEntity->getId()) { // grant permissions only for newly created dashboards
                foreach ($admins as $admin) {
                    $permissions = new Permission();
                    $permissions->setDashboard($dashboardEntity)->setPerson($admin)->setName(Permission::FULL);
                    if (!$dashboardEntity->getPerson()) {
                        $dashboardEntity->setPerson($admin);
                    }
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
            }
            $this->getEm()->flush();
        }
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function runReset()
    {
        $dashboards = $this->getEm()->getRepository(Dashboard::class)->findBy(['is_default' => 1]);
        foreach ($dashboards as $dashboard) {
            $this->getEm()->remove($dashboard);
        }
        $this->getEm()->flush();
        $this->runInstall();
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
        if (!empty($dashboard['system_name'])) {
            $dashboardEntity->setSystemName($dashboard['system_name']);
        }
        if (!empty($dashboard['display_order'])) {
            $dashboardEntity->setDisplayOrder($dashboard['display_order']);
        }
        foreach ($dashboard['reports'] as $report) {
            $tab = new Tab();
            $tab
                ->setTitle($report['title'])
                ->setDashboard($dashboardEntity)
                ->setSortOrder($report['sort_order']);
            $this->getEm()->persist($tab);
            if (isset($report['widgets'])) {
                foreach ($report['widgets'] as $widget) {
                    $size = implode(':', array_map(function ($v) {
                        return DashboardData::UNIT_SIZE * $v;
                    }, explode(':', $widget['size'])));
                    $position = implode(':', array_map(function ($v) {
                        return DashboardData::UNIT_SIZE * $v;
                    }, explode(':', $widget['position'])));

                    $widgetEntity = new Widget();
                    $widgetEntity
                        ->setTitle($widget['title'])
                        ->setSize($size)
                        ->setPosition($position)
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
        $this->runInstall();
    }
}

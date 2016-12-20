<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

use Application\DeskPRO\Entity\Person as Person;
use Application\DeskPRO\Entity\ReportDashboard as Dashboard;
use Application\DeskPRO\Entity\ReportDashboardPermission as Permission;
use Application\DeskPRO\Entity\ReportDashboardReport as Tab;
use Application\DeskPRO\Entity\ReportDashboardWidget as Widget;
use Application\DeskPRO\Entity\ReportWidget as WidgetPrototype;

class DashboardData extends AbstractDefaultData
{
    const PRIORITY = 20000;

    protected $dashboards = [
        [
            'title'   => 'Ticket insights',
            'reports' => [
                [
                    'title'      => 'Overview',
                    'columns'    => 10,
                    'sort_order' => 1,
                    'widgets'    => [
                        [
                            'title'     => 'Test widget 1',
                            'position'  => '0:0',
                            'size'      => '5:2',
                            'type'      => 'simple_area',
                            'widget_id' => 8,
                        ],
                        [
                            'title'     => 'Test widget 2',
                            'position'  => '0:5',
                            'size'      => '5:2',
                            'type'      => 'simple_bars',
                            'widget_id' => 8,
                        ],
                        [
                            'title'     => 'Test widget 3',
                            'position'  => '2:0',
                            'size'      => '5:2',
                            'type'      => 'simple_lines',
                            'widget_id' => 31,
                            'variables' => [
                                ['placeholder' => 'field group', 'value' => 'department'],
                            ],
                        ],
                        [
                            'title'     => 'Test widget 4',
                            'position'  => '2:5',
                            'size'      => '5:2',
                            'type'      => 'simple_area',
                            'widget_id' => 31,
                            'variables' => [['placeholder' => 'field group', 'value' => 'department']],
                        ],
                        [
                            'title'     => 'Test widget 5',
                            'position'  => '4:0',
                            'size'      => '5:2',
                            'type'      => 'simple_bars',
                            'widget_id' => 31,
                            'variables' => [['placeholder' => 'field group', 'value' => 'department']],
                        ],
                        [
                            'title'     => 'Test widget 6',
                            'position'  => '4:5',
                            'size'      => '5:2',
                            'type'      => 'simple_area',
                            'widget_id' => 31,
                            'variables' => [['placeholder' => 'field group', 'value' => 'department']],
                        ],
                        [
                            'title'     => 'Test widget 7',
                            'position'  => '6:0',
                            'size'      => '10:3',
                            'type'      => 'simple_lines',
                            'widget_id' => 31,
                            'variables' => [['placeholder' => 'field group', 'value' => 'department']],
                        ],
                    ],
                ],
                [
                    'title'      => 'Agent Performance',
                    'columns'    => 10,
                    'sort_order' => 2,
                    'widgets'    => [
                        [
                            'title'    => 'Agent Activity',
                            'position' => '0:0',
                            'size'     => '10:3',
                            'hc_data'  => 'performance:agent_activity',
                        ],
                        [
                            'title'    => 'Agent Hours',
                            'position' => '4:0',
                            'size'     => '10:3',
                            'hc_data'  => 'performance:agent_hours',
                        ],
                    ],
                ],
                [
                    'title'      => 'Ticket Satisfaction',
                    'columns'    => 10,
                    'sort_order' => 3,
                    'widgets'    => [
                        [
                            'title'    => 'Feedback',
                            'position' => '0:0',
                            'size'     => '10:3',
                            'hc_data'  => 'ticket_satisfaction:feed',
                        ],
                        [
                            'title'    => 'Summary',
                            'position' => '4:0',
                            'size'     => '10:3',
                            'hc_data'  => 'ticket_satisfaction:summary',
                        ],
                    ],
                ],
                [
                    'title'      => 'Billing',
                    'columns'    => 10,
                    'sort_order' => 3,
                    'widgets'    => [
                    ],
                ],
            ],
        ],
        [
            'title'   => 'Chat insights',
            'reports' => [
                [
                    'title'      => 'Overview',
                    'columns'    => 10,
                    'sort_order' => 1,
                    'widgets'    => [
                    ],
                ],
                [
                    'title'      => 'Chat Satisfaction',
                    'columns'    => 10,
                    'sort_order' => 2,
                    'widgets'    => [
                    ],
                ],
            ],
        ],
    ];

    public function runInstall()
    {
        /** @var Person $admin */
        $admin  = $this->getEm()->getRepository('DeskPRO:Person')->find(1);
        $agents = $this->getEm()->getRepository('DeskPRO:Person')->findBy(['is_agent' => 1]);
        foreach ($this->dashboards as $dashboard) {
            $dashboardEntity = new Dashboard();
            $dashboardEntity->setTitle($dashboard['title'])->setDefault(true);
            foreach ($dashboard['reports'] as $report) {
                $tab = new Tab();
                $tab
                    ->setTitle($report['title'])
                    ->setColumns($report['columns'])
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
                        if (isset($widget['hc_data'])) {
                            $widgetEntity->setHcData($widget['hc_data']);
                            $widgetEntity->setType(Widget::WIDGET_TYPE_HARDCODED);
                        } elseif (isset($widget['widget_id'])) {
                            /** @var WidgetPrototype $widgetPrototype */
                            $widgetPrototype = $this->getEm()->getRepository('DeskPRO:ReportWidget')->find($widget['widget_id']);
                            $widgetEntity->setWidget($widgetPrototype);
                            $widgetEntity->setType($widget['type']);
                        }
                        if (isset($widget['variables'])) {
                            $widgetEntity->setVariables($widget['variables']);
                        }

                        $this->getEm()->persist($widgetEntity);
                    }
                }
            }
            $this->getEm()->persist($dashboardEntity);
            $permissions = new Permission();
            $permissions->setDashboard($dashboardEntity)->setPerson($admin)->setName(Permission::FULL);
            $this->getEm()->persist($permissions);
            foreach ($agents as $agent) {
                if ($agent->getId() != 1) {
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

    public function runReset()
    {
        $dashboards = $this->getEm()->getRepository('DeskPRO:ReportDashboard')->findBy(['is_default' => 1]);
        foreach ($dashboards as $dashboard) {
            $this->getEm()->remove($dashboard);
        }
        $this->getEm()->flush();
    }

    public function runSync()
    {
        $dashboards = $this->getEm()->getRepository('DeskPRO:ReportDashboard')->findBy(['is_default' => 1]);
        if (count($dashboards) < count($this->dashboards)) {
            $this->runReset();
            $this->runInstall();
        }
    }
}

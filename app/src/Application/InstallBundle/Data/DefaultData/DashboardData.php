<?php
/**
 * Created by PhpStorm.
 * User: Den
 * Date: 21.12.2014
 * Time: 18:23
 */
namespace Application\InstallBundle\Data\DefaultData;

use Application\DeskPRO\Entity\ReportDashboard as Dashboard;
use Application\DeskPRO\Entity\ReportDashboardReport as Tab;
use Application\DeskPRO\Entity\ReportDashboardWidget as Widget;
use Application\DeskPRO\Entity\ReportWidget as WidgetPrototype;
use Application\DeskPRO\Entity\ReportDashboardPermission as Permission;
use Application\DeskPRO\Entity\Person as Person;

class DashboardData extends AbstractDefaultData
{
    protected $dashboards = array(
        array(
            'title' => 'Ticket insights',
            'reports' => array(
                array(
                    'title' => 'Overview',
                    'columns' => 10,
                    'sort_order' => 1,
                    'widgets' => array(
                        array(
                            'title' => 'Tickets awaiting agent',
                            'position' => "0:0",
                            'size'     => '5:2',
                            'hc_data'  => 'overview:tickets_awaiting_agent',
                            'variables' => array('test'=>'test', 'test1'=>'test1'),
                        ),
                        array(
                            'title' => 'Tickets Resolved',
                            'position' => "0:5",
                            'size'     => '5:2',
                            'hc_data'  => 'overview:tickets_resolved',
                        ),
                        array(
                            'title' => 'Tickets Response Time',
                            'position' => "2:0",
                            'size'     => '5:2',
                            'hc_data'  => 'overview:tickets_response_time',
                        ),
                        array(
                            'title' => 'Tickets User Waiting Time',
                            'position' => "2:5",
                            'size'     => '5:2',
                            'hc_data'  => 'overview:tickets_user_waiting_time',
                        ),
                        array(
                            'title' => 'Tickets Opened Hour',
                            'position' => "4:0",
                            'size'     => '5:2',
                            'hc_data'  => 'overview:tickets_opened_hour',
                        ),
                        array(
                            'title' => 'Tickets SLA Status',
                            'position' => "4:5",
                            'size'     => '5:2',
                            'hc_data'  => 'overview:tickets_sla_status',
                        ),
                        array(
                            'title' => 'Chats Created',
                            'position' => "6:0",
                            'size'     => '10:3',
                            'hc_data'  => 'overview:chats_created',
                        ),
                    ),
                ),
                array(
                    'title' => 'Agent Performance',
                    'columns' => 10,
                    'sort_order' => 2,
                    'widgets' => array(
                        array(
                            'title' => 'Agent Activity',
                            'position' => "0:0",
                            'size'     => '10:3',
                            'hc_data'  => 'performance:agent_activity',
                        ),
                        array(
                            'title' => 'Agent Hours',
                            'position' => "4:0",
                            'size'     => '10:3',
                            'hc_data'  => 'performance:agent_hours',
                        ),
                    ),
                ),
                array(
                    'title' => 'Ticket Satisfaction',
                    'columns' => 10,
                    'sort_order' => 3,
                    'widgets' => array(
                        array(
                            'title' => 'Feedback',
                            'position' => "0:0",
                            'size'     => '10:3',
                            'hc_data'  => 'ticket_satisfaction:feed',
                        ),
                        array(
                            'title' => 'Summary',
                            'position' => "4:0",
                            'size'     => '10:3',
                            'hc_data'  => 'ticket_satisfaction:summary',
                        ),
                    ),
                ),
                array(
                    'title' => 'Billing',
                    'columns' => 10,
                    'sort_order' => 3,
                    'widgets' => array(
                    ),
                ),
            ),
        ),
        array(
            'title' => 'Chat insights',
            'reports' => array(
                array(
                    'title' => 'Overview',
                    'columns' => 10,
                    'sort_order' => 1,
                    'widgets' => array(
                    ),
                ),
                array(
                    'title' => 'Chat Satisfaction',
                    'columns' => 10,
                    'sort_order' => 2,
                    'widgets' => array(
                    ),
                ),
            ),
        ),
    );

    public function runInstall()
    {

        /** @var WidgetPrototype $widgetPrototype */
        $widgetPrototype = $this->getEm()->getRepository('DeskPRO:ReportWidget')->find(1);
        /** @var Person $admin */
        $admin = $this->getEm()->getRepository('DeskPRO:Person')->find(1);
        $agents = $this->getEm()->getRepository('DeskPRO:Person')->findBy(array('is_agent'=>1));
        foreach($this->dashboards as $dashboard)
        {
            $dashboardEntity = new Dashboard();
            $dashboardEntity->setTitle($dashboard['title'])->setDefault(true);
            foreach($dashboard['reports'] as $report)
            {
                $tab = new Tab();
                $tab
                    ->setTitle($report['title'])
                    ->setColumns($report['columns'])
                    ->setDashboard($dashboardEntity)
                    ->setSortOrder($report['sort_order']);
                $this->getEm()->persist($tab);
                foreach($report['widgets'] as $widget) {
                    $widgetEntity = new Widget();
                    $widgetEntity
                        ->setTitle($widget['title'])
                        ->setSize($widget['size'])
                        ->setPosition($widget['position'])
                        ->setReport($tab);
                        if(isset($widget['hc_data'])) {
                            $widgetEntity->setHcData($widget['hc_data']);
                            $widgetEntity->setType(Widget::WIDGET_TYPE_HARDCODED);
                        } else {
                            $widgetEntity->setWidget($widgetPrototype);
                            $widgetEntity->setType(Widget::WIDGET_TYPE_BAR);
                        }
                    if(isset($widget['variables'])) {
                        $widgetEntity->setVariables($widget['variables']);
                    }

                    $this->getEm()->persist($widgetEntity);
                }
            }
            $this->getEm()->persist($dashboardEntity);
            $permissions = new Permission();
            $permissions->setDashboard($dashboardEntity)->setPerson($admin)->setName(Permission::FULL);
            $this->getEm()->persist($permissions);
            foreach($agents as $agent) {
                if($agent->getId() != 1) {
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
        $dashboards = $this->getEm()->getRepository('DeskPRO:ReportDashboard')->findBy(array('is_default'=>1));
        foreach($dashboards as $dashboard) {
            $this->getEm()->remove($dashboard);
        }
        $this->getEm()->flush();
    }

    public function runSync()
    {
        $dashboards = $this->getEm()->getRepository('DeskPRO:ReportDashboard')->findBy(array('is_default' => 1));
        if(count($dashboards) < count($this->dashboards))
        {
            $this->runReset();
            $this->runInstall();
        }
    }
}
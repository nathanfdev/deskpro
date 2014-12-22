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
use Application\DeskPRO\Entity\ReportBuilder as WidgetPrototype;
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
                            'title' => 'Overview1',
                            'position' => "0:0",
                            'size'     => '4:3',
                        ),
                        array(
                            'title' => 'Overview2',
                            'position' => "0:4",
                            'size'     => '6:3',
                        ),
                    ),
                ),
                array(
                    'title' => 'Agent Performance',
                    'columns' => 10,
                    'sort_order' => 2,
                    'widgets' => array(
                        array(
                            'title' => 'Agent Performance1',
                            'position' => "0:0",
                            'size'     => '4:3',
                        ),
                        array(
                            'title' => 'Agent Performance2',
                            'position' => "0:4",
                            'size'     => '6:3',
                        ),
                    ),
                ),
                array(
                    'title' => 'Ticket Satisfaction',
                    'columns' => 10,
                    'sort_order' => 3,
                    'widgets' => array(
                        array(
                            'title' => 'Ticket Satisfaction1',
                            'position' => "0:0",
                            'size'     => '4:3',
                        ),
                        array(
                            'title' => 'Ticket Satisfaction2',
                            'position' => "0:4",
                            'size'     => '6:3',
                        ),
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
                        array(
                            'title' => 'Overview1',
                            'position' => "0:0",
                            'size'     => '4:3',
                        ),
                        array(
                            'title' => 'Overview2',
                            'position' => "0:4",
                            'size'     => '6:3',
                        ),
                        array(
                            'title' => 'Overview3',
                            'position' => "3:0",
                            'size'     => '4:3',
                        ),
                        array(
                            'title' => 'Overview4',
                            'position' => "3:4",
                            'size'     => '6:3',
                        ),
                    ),
                ),
                array(
                    'title' => 'Chat Satisfaction',
                    'columns' => 10,
                    'sort_order' => 2,
                    'widgets' => array(
                        array(
                            'title' => 'Chat Satisfaction',
                            'position' => "0:0",
                            'size'     => '4:3',
                        ),
                        array(
                            'title' => 'Chat Satisfaction',
                            'position' => "0:4",
                            'size'     => '6:3',
                        ),
                        array(
                            'title' => 'Chat Satisfaction',
                            'position' => "3:0",
                            'size'     => '4:3',
                        ),
                        array(
                            'title' => 'Chat Satisfaction',
                            'position' => "3:4",
                            'size'     => '6:3',
                        ),
                    ),
                ),
            ),
        ),
    );

    public function runInstall()
    {

        /** @var WidgetPrototype $widgetPrototype */
        $widgetPrototype = $this->getEm()->getRepository('DeskPRO:ReportBuilder')->find(1);
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
                        ->setReport($tab)
                        ->setWidget($widgetPrototype);
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
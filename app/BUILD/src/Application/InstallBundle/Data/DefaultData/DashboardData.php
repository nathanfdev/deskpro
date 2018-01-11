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
                    'columns'    => 24,
                    'sort_order' => 1,
                ],
            ],
        ],
        [
            'title'   => 'Chat insights',
            'reports' => [
                [
                    'title'      => 'Overview',
                    'columns'    => 24,
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
                            $widgetPrototype = $this->getEm()->getRepository(ReportWidget::class)->find($widget['widget_id']);
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
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function runSync()
    {
        $dashboards = $this->getEm()->getRepository(Dashboard::class)->findBy(['is_default' => 1]);
        if (count($dashboards) < count($this->dashboards)) {
            $this->runReset();
            $this->runInstall();
        }
    }
}

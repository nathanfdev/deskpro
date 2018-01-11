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

namespace DeskPRO\Bundle\AppBundle\Form\Type\Reports;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\ReportDashboard;
use Application\DeskPRO\Entity\ReportDashboardReport;
use DeskPRO\Bundle\ReportBundle\Dashboard\DashboardManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ReportDashboardReportCollectionType.
 */
class ReportDashboardReportCollectionType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var DashboardManager
     */
    private $dashboardManager;

    /**
     * Constructor.
     *
     * @param EntityManager    $em
     * @param DashboardManager $dashboardManager
     */
    public function __construct(EntityManager $em, DashboardManager $dashboardManager)
    {
        $this->em               = $em;
        $this->dashboardManager = $dashboardManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit'], 100);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'error_bubbling' => false,
                'allow_add'      => true,
                'allow_delete'   => true,
                'entry_type'     => ReportDashboardReportType::class,
                'entry_options'  => function (Options $options) {
                    return [
                        'error_bubbling' => false,
                        'dashboard'      => $options['dashboard'],
                        'person'         => $options['person'],
                        'collection'     => true,
                    ];
                },
            ])
            ->setRequired(['person', 'dashboard'])
            ->setAllowedTypes('person', Person::class)
            ->setAllowedTypes('dashboard',  ReportDashboard::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::class;
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        /** @var ReportDashboard $dashboard */
        $dashboard = $form->getConfig()->getOption('dashboard');

        if (is_array($data)) {
            $resolved  = [];
            $sortOrder = 0;
            $existIds  = [];

            // add new reports
            foreach ($data as $reportData) {
                if (array_key_exists('id', $reportData)) {
                    $report = $dashboard->getReports()->filter(function (ReportDashboardReport $report) use ($reportData) {
                        return $report->getId() === (int) $reportData['id'];
                    })->first();

                    /** @var ReportDashboardReport $report */
                    $report = $report ?: null;
                    if ($report) {
                        $existIds[] = $report->getId();
                        unset($reportData['id']);
                    } else {
                        $report = new ReportDashboardReport();
                    }
                } elseif (array_key_exists('clone_id', $reportData)) {
                    $report = $this->em->getRepository(ReportDashboardReport::class)->find($reportData['clone_id']);
                    if ($report) {
                        $report = $this->dashboardManager->cloneReport($report);
                        unset($reportData['clone_id']);
                    } else {
                        $report = new ReportDashboardReport();
                    }
                } else {
                    $report = new ReportDashboardReport();
                }

                if ($report instanceof ReportDashboardReport) {
                    $report->setSortOrder($sortOrder);
                    $resolved[] = $report;
                    $sortOrder += 10;
                }
            }

            $event->setData($data);
            $form->setData($resolved);

            // remove deleted ones
            foreach ($dashboard->getReports() as $report) {
                if ($report->getId() && !in_array($report->getId(), $existIds)) {
                    $dashboard->removeReport($report);
                }
            }
        }
    }
}

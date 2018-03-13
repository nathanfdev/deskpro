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
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ReportDashboardReportType.
 */
class ReportDashboardReportType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
            ])
            ->add('variables', CollectionType::class, [
                'entry_type'     => ReportWidgetVariableType::class,
                'allow_add'      => true,
                'allow_delete'   => true,
                'error_bubbling' => false,
                'required'       => false,
            ])
            ->add('schedule', ScheduledReportType::class, [
                'required'    => false,
                'mapped'      => false,
                'person'      => $options['person'],
                'constraints' => [
                    new Assert\Valid(),
                ],
            ])
        ;

        if (!$options['dashboard']) {
            $builder->add('dashboard', EntityType::class, [
                'class'         => ReportDashboard::class,
                'required'      => true,
                'query_builder' => function (EntityRepository $er) use ($options) {
                    return $er
                        ->createQueryBuilder('d')
                        ->join('d.permissions', 'p')
                        ->where(
                            'd.is_default = 0',
                            'p.person IN (:person)'
                        )
                        ->setParameter('person', $options['person'])
                    ;
                },
            ]);
        }

        if ($options['collection']) {
            // add fields to map validation errors on collection manipulates
            $builder
                ->add('id', EntityType::class, [
                    'class'    => ReportDashboardReport::class,
                    'required' => false,
                    'mapped'   => false,
                ])
                ->add('clone_id', EntityType::class, [
                    'class'    => ReportDashboardReport::class,
                    'required' => false,
                    'mapped'   => false,
                ])
            ;
        }

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => ReportDashboardReport::class,
                'dashboard'  => null,
                'collection' => false,
            ])
            ->setRequired('person')
            ->setAllowedTypes('person', Person::class)
            ->setAllowedTypes('dashboard', ['null', ReportDashboard::class])
        ;
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $form    = $event->getForm();
        $data    = $event->getData();
        $options = $form->getConfig()->getOptions();

        /** @var Person $person */
        $person = $form->getConfig()->getOption('person');

        if ($data instanceof ReportDashboardReport) {
            // set dashboard from the parent form
            $dashboard = $options['dashboard'];
            if ($dashboard) {
                $data->setDashboard($dashboard);
            } else {
                $dashboard = $data->getDashboard();
            }

            // set sort order for new reports
            if (!$data->getId()) {
                $dashboard = $data->getDashboard();
                if ($dashboard) {
                    $maxSortOrder = 0;
                    foreach ($dashboard->getReports() as $report) {
                        $maxSortOrder = max($maxSortOrder, $report->getSortOrder());
                    }

                    $data->setSortOrder(count($dashboard->getReports()) ? $maxSortOrder + 10 : 0);
                }
            }

            // set person's schedule
            if ($form->get('schedule')->isSubmitted()) {
                $schedule = $form->get('schedule')->getData();
                if ($schedule) {
                    $data->addSchedule($schedule);
                } else {
                    $data->removePersonSchedule($options['person']);
                }
            }
        }
    }
}

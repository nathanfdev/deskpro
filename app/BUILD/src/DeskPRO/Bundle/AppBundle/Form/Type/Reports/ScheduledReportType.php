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
use Application\DeskPRO\Reports\ReportSaver;
use DeskPRO\Bundle\AppBundle\Entity\Report\ScheduledReport;
use DeskPRO\Bundle\AppBundle\Form\Type\Reports\ScheduleTime\ScheduleBimonthlyType;
use DeskPRO\Bundle\AppBundle\Form\Type\Reports\ScheduleTime\ScheduleDailyType;
use DeskPRO\Bundle\AppBundle\Form\Type\Reports\ScheduleTime\ScheduleMonthlyType;
use DeskPRO\Bundle\AppBundle\Form\Type\Reports\ScheduleTime\ScheduleWeeklyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ScheduledReportType.
 */
class ScheduledReportType extends AbstractType
{
    /**
     * @var ReportSaver
     */
    private $reportSaver;

    /**
     * Constructor.
     *
     * @param ReportSaver $reportSaver
     */
    public function __construct(ReportSaver $reportSaver)
    {
        $this->reportSaver = $reportSaver;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('frequency', ChoiceType::class, [
                'required'          => true,
                'choices_as_values' => true,
                'choices'           => [
                    ScheduledReport::FREQUENCY_DAILY,
                    ScheduledReport::FREQUENCY_WEEKLY,
                    ScheduledReport::FREQUENCY_MONTHLY,
                    ScheduledReport::FREQUENCY_BIMONTHLY,
                ],
            ])
            ->add('send_to', CollectionType::class, [
                'property_path'  => 'sendTo',
                'entry_type'     => EmailType::class,
                'required'       => true,
                'allow_add'      => true,
                'allow_delete'   => true,
                'error_bubbling' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => ScheduledReport::class,
            ])
            ->setRequired('person')
            ->setAllowedTypes('person', Person::class)
        ;
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

        if (isset($data['frequency'])) {
            switch ($data['frequency']) {
                case ScheduledReport::FREQUENCY_DAILY:
                    $form->add('when', ScheduleDailyType::class, [
                        'required'      => true,
                        'property_path' => 'whenSetting',
                    ]);
                    break;
                case ScheduledReport::FREQUENCY_WEEKLY:
                    $form->add('when', ScheduleWeeklyType::class, [
                        'required'      => true,
                        'property_path' => 'whenSetting',
                    ]);
                    break;
                case ScheduledReport::FREQUENCY_MONTHLY:
                    $form->add('when', ScheduleMonthlyType::class, [
                        'required'      => true,
                        'property_path' => 'whenSetting',
                    ]);
                    break;
                case ScheduledReport::FREQUENCY_BIMONTHLY:
                    $form->add('when', ScheduleBimonthlyType::class, [
                        'required'      => true,
                        'property_path' => 'whenSetting',
                    ]);
                    break;
            }
        }
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        /** @var Person $person */
        $person = $form->getConfig()->getOption('person');

        if ($data instanceof ScheduledReport) {
            $data->setPerson($person);
            $data->setWhenTz($person->getTimezone());
            $data->setNextSendDate(
                $this->reportSaver->calculateNextSendDate(
                    $data->getWhenSetting(),
                    $data->getWhenTz(),
                    $data->getFrequency()
                )
            );
        }
    }
}

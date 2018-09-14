<?php

namespace DeskPRO\Bundle\ReportBundle\Form\Type;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\Report\ScheduledReport;
use DeskPRO\Bundle\ReportBundle\Form\Type\ScheduleTime\ScheduleBimonthlyType;
use DeskPRO\Bundle\ReportBundle\Form\Type\ScheduleTime\ScheduleDailyType;
use DeskPRO\Bundle\ReportBundle\Form\Type\ScheduleTime\ScheduleMonthlyType;
use DeskPRO\Bundle\ReportBundle\Form\Type\ScheduleTime\ScheduleWeeklyType;
use DeskPRO\Bundle\ReportBundle\Service\ReportSaver;
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
     * @var \DeskPRO\Bundle\ReportBundle\Service\ReportSaver
     */
    private $reportSaver;

    /**
     * Constructor.
     *
     * @param \DeskPRO\Bundle\ReportBundle\Service\ReportSaver $reportSaver
     */
    public function __construct(\DeskPRO\Bundle\ReportBundle\Service\ReportSaver $reportSaver)
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

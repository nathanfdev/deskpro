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
use Application\DeskPRO\Entity\ReportDashboardReport;
use Application\DeskPRO\Entity\ReportDashboardWidget;
use Application\DeskPRO\Entity\ReportWidget;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ReportDashboardWidgetType.
 */
class ReportDashboardWidgetType extends AbstractType
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
            ->add('type', ChoiceType::class, [
                'required'          => true,
                'choices_as_values' => true,
                'choices'           => [
                    ReportDashboardWidget::TYPE_SIMPLE_BARS,
                    ReportDashboardWidget::TYPE_BARS,
                    ReportDashboardWidget::TYPE_SIMPLE_LINES,
                    ReportDashboardWidget::TYPE_LINES,
                    ReportDashboardWidget::TYPE_AREA,
                    ReportDashboardWidget::TYPE_SIMPLE_AREA,
                    ReportDashboardWidget::TYPE_PIE,
                    ReportDashboardWidget::TYPE_TABLE,
                    ReportDashboardWidget::TYPE_SIMPLE_STAT,
                ],
            ])
            ->add('widget', EntityType::class, [
                'class'    => ReportWidget::class,
                'required' => true,
            ])
            ->add('size_x', IntegerType::class, [
                'required' => true,
            ])
            ->add('size_y', IntegerType::class, [
                'required' => true,
            ])
            ->add('row', IntegerType::class, [
                'required' => true,
            ])
            ->add('col', IntegerType::class, [
                'required' => true,
            ])
            ->add('widget_variables', CollectionType::class, [
                'property_path'  => 'variables',
                'entry_type'     => ReportWidgetVariableType::class,
                'allow_add'      => true,
                'allow_delete'   => true,
                'error_bubbling' => false,
                'required'       => false,
            ])
            ->add('options', TextType::class, [
                'required' => false,
            ])
        ;

        if (!$options['report']) {
            $builder->add('report', EntityType::class, [
                'class'         => ReportDashboardReport::class,
                'required'      => true,
                'query_builder' => function (EntityRepository $er) use ($options) {
                    return $er
                        ->createQueryBuilder('r')
                        ->join('r.dashboard', 'd')
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

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => ReportDashboardWidget::class,
                'report'     => null,
            ])
            ->setRequired('person')
            ->setAllowedTypes('report', ['null', ReportDashboardReport::class])
            ->setAllowedTypes('person', Person::class)
        ;
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

        if ($data instanceof ReportDashboardWidget) {
            // set report relation
            $report = $form->getConfig()->getOption('report');
            if ($report) {
                $data->setReport($report);
            }

            // merge vars metadata from the widget
            $widget = $data->getWidget();
            if ($widget) {
                $widgetVars = [];
                foreach ($widget->getVariables() as $variable) {
                    foreach ($data->getVariables() as $dashboardVariable) {
                        if ($dashboardVariable['name'] === $variable['name']) {
                            $variable['value'] = $dashboardVariable['value'];
                            $widgetVars[]      = $variable;
                        }
                    }
                }

                $data->setVariables($widgetVars);
            }
        }
    }
}

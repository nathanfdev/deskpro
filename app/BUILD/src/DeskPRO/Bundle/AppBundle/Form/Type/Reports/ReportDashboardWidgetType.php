<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Reports;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\ReportDashboardReport;
use Application\DeskPRO\Entity\ReportDashboardWidget;
use Application\DeskPRO\Entity\ReportWidget;
use DeskPRO\Bundle\ReportBundle\Dashboard\DashboardWidgetManager;
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
                    DashboardWidgetManager::WIDGET_RENDER_TYPE_BAR,
                    DashboardWidgetManager::WIDGET_RENDER_TYPE_LINE,
                    DashboardWidgetManager::WIDGET_RENDER_TYPE_AREA,
                    DashboardWidgetManager::WIDGET_RENDER_TYPE_PIE,
                    DashboardWidgetManager::WIDGET_RENDER_TYPE_GAUGE,
                    DashboardWidgetManager::WIDGET_RENDER_TYPE_STAT,
                    DashboardWidgetManager::WIDGET_RENDER_TYPE_TABLE,
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
            ->add('js_code', TextType::class, [
                'property_path' => 'jsCode',
                'required'      => false,
            ])
        ;

        if (!$options['report']) {
            $builder->add('report', EntityType::class, [
                'class'         => ReportDashboardReport::class,
                'required'      => true,
                'query_builder' => function (EntityRepository $er) use ($options) {
                    $qb = $er->createQueryBuilder('r');
                    if (!$options['person']->isAdmin() && !$options['person']->can_reports) {
                        $qb
                            ->join('r.dashboard', 'd')
                            ->join('d.permissions', 'p')
                            ->andWhere('d.is_default = 0')
                            ->andWhere('p.person IN (:person) OR p.team IN (:teams) OR d.person IN (:person)')
                            ->orWhere('p.person IS NULL AND p.team IS NULL AND p.department IS NULL')
                            ->setParameter('person', $options['person'])
                            ->setParameter('teams', $options['person']->getTeams())
                        ;
                    }

                    return $qb;
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

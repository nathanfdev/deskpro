<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
 */

namespace Application\DeskPRO\Reports\Form\Type;

use Application\DeskPRO\Entity\ReportWidget;
use Application\LegacyApiBundle\Service\Dashboard;
use Application\LegacyApiBundle\Service\DashboardWidget;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ReportWidgetType.
 */
class ReportWidgetType extends AbstractType
{
    /**
     * @var Dashboard
     */
    private $dashboard;

    /**
     * ReportWidgetType constructor.
     *
     * @param Dashboard $dashboard
     */
    public function __construct(Dashboard $dashboard)
    {
        $this->dashboard = $dashboard;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, ['required' => true])
            ->add('description', TextType::class, ['required' => false])
            ->add('display_types', ChoiceType::class, [
                'choices' => [
                    DashboardWidget::WIDGET_RENDER_TYPE_AREA,
                    DashboardWidget::WIDGET_RENDER_TYPE_BAR,
                    DashboardWidget::WIDGET_RENDER_TYPE_LINE,
                    DashboardWidget::WIDGET_RENDER_TYPE_PIE,
                    DashboardWidget::WIDGET_RENDER_TYPE_TABLE,
                ],
                'multiple'          => true,
                'choices_as_values' => true,
                'required'          => true,
            ])
            ->add('labels', CollectionType::class, [
                'entry_type'     => TextType::class,
                'allow_add'      => true,
                'allow_delete'   => true,
                'error_bubbling' => false,
            ])
            ->add('variables', CollectionType::class, [
                'entry_type'     => ReportWidgetVariableType::class,
                'allow_add'      => true,
                'allow_delete'   => true,
                'error_bubbling' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'transformLabels']);
    }

    /**
     * @param FormEvent $event
     */
    public function transformLabels(FormEvent $event)
    {
        $data = $event->getData();
        if (isset($data['labels']) && is_array($data['labels'])) {
            foreach ($data['labels'] as &$label) {
                $label = $this->dashboard->mapLabelToSystemName($label);
            }
        }
        $event->setData($data);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => ReportWidget::class,
            ]
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'form_dashboards_report_widget';
    }
}

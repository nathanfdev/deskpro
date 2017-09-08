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
use Application\LegacyApiBundle\Service\DashboardWidget;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
     * @var DashboardWidget
     */
    private $dashboardWidget;

    /**
     * ReportWidgetType constructor.
     *
     * @param DashboardWidget $dashboardWidget
     */
    public function __construct(DashboardWidget $dashboardWidget)
    {
        $this->dashboardWidget = $dashboardWidget;
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
            ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'transformDisplayTypes']);
    }

    public function transformDisplayTypes(FormEvent $event)
    {
        /** @var ReportWidget $data */
        $data             = $event->getData();
        $transformedTypes = [];
        // this is just a stub for now, we need to update our way to determine display types
        foreach ($data->getDisplayTypes() as $displayType) {
            $transformedTypes[] = $this->dashboardWidget->getReversedWidgetGraphType($displayType);
        }

        $data->setDisplayTypes($transformedTypes);
    }

/**
 * @param OptionsResolver $resolver
 */public function configureOptions(OptionsResolver $resolver)
{
    $resolver->setDefaults(
            [
                'data_class' => ReportWidget::class,
            ]
        );
}

    public function getName()
    {
        return 'form_dashboards_report_widget';
    }
}

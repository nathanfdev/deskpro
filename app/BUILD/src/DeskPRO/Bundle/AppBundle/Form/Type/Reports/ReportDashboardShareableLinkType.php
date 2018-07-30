<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Reports;

use Application\DeskPRO\Entity\ReportDashboard;
use Application\DeskPRO\Entity\ReportDashboardReport;
use Application\DeskPRO\Entity\ReportDashboardShareableLink;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ReportDashboardShareableLinkType.
 */
class ReportDashboardShareableLinkType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextareaType::class, [
                'required' => true,
            ])
            ->add('dashboard', EntityType::class, [
                'required' => true,
                'class'    => ReportDashboard::class,
            ])
            ->add('who_can_use', ChoiceType::class, [
                'required'          => false,
                'property_path'     => 'whoCanUse',
                'empty_data'        => ReportDashboardShareableLink::USE_ANYONE,
                'choices_as_values' => true,
                'choices'           => [
                    ReportDashboardShareableLink::USE_ANYONE,
                    ReportDashboardShareableLink::USE_WHITELIST,
                ],
            ])
            ->add('ip_whitelist', CollectionType::class, [
                'required'      => false,
                'property_path' => 'ipWhitelist',
                'entry_type'    => TextType::class,
                'allow_add'     => true,
                'allow_delete'  => true,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ReportDashboardShareableLink::class,
        ]);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        $dashboard = null;
        $shareLink = $form->getData();
        if ($shareLink instanceof ReportDashboardShareableLink && $shareLink->getDashboard()) {
            $dashboard = $shareLink->getDashboard()->getId();
        }
        if (isset($data['dashboard'])) {
            $dashboard = $data['dashboard'];
        }

        if ($dashboard) {
            $form->add('default_report', EntityType::class, [
                'required'      => false,
                'property_path' => 'defaultReport',
                'class'         => ReportDashboardReport::class,
                'query_builder' => function (EntityRepository $er) use ($dashboard) {
                    $qb = $er
                        ->createQueryBuilder('r')
                        ->join('r.dashboard', 'd')
                        ->where('d.id = :dashboard_id')
                        ->setParameter('dashboard_id', $dashboard)
                    ;

                    return $qb;
                },
            ]);
        }

        if (isset($data['ip_whitelist']) && is_string($data['ip_whitelist'])) {
            $data['ip_whitelist'] = explode(',', $data['ip_whitelist']);
            $data['ip_whitelist'] = array_map('trim', $data['ip_whitelist']);
        }

        $event->setData($data);
    }
}

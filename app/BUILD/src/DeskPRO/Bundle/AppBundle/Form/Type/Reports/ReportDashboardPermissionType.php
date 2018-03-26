<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Reports;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\ReportDashboard;
use Application\DeskPRO\Entity\ReportDashboardPermission;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ReportDashboardPermissionType.
 */
class ReportDashboardPermissionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('person', EntityType::class, [
                'class'    => Person::class,
                'required' => false,
            ])
            ->add('team', EntityType::class, [
                'class'    => AgentTeam::class,
                'required' => false,
            ])
            ->add('department', EntityType::class, [
                'class'    => Department::class,
                'required' => false,
            ])
            ->add('name', ChoiceType::class, [
                'required'          => true,
                'choices_as_values' => true,
                'choices'           => [ReportDashboardPermission::VIEW, ReportDashboardPermission::FULL],
            ])
            ->add('view_all', ApiBooleanType::class, [
                'property_path' => 'viewAll',
                'required'      => false,
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => ReportDashboardPermission::class,
            ])
            ->setRequired('dashboard')
            ->setAllowedTypes('dashboard', ReportDashboard::class)
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

        if ($data instanceof ReportDashboardPermission) {
            $data->setDashboard($form->getConfig()->getOption('dashboard'));
        }
    }
}

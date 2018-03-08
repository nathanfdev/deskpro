<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
 */

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
use Symfony\Component\Form\FormError;
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
        // default dashboards are still editable for admins (they can change permissions)
        $choices = [ReportDashboardPermission::VIEW, ReportDashboardPermission::FULL];

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
                'choices'           => $choices,
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
            if (
                // default dashboard may be edited by admins only, no full access for teams, departments or for all
                $data->getDashboard()->isDefault() &&
                (
                    ($data->getPerson() && !$data->getPerson()->isAdmin()) ||
                    $data->getTeam() || $data->getDepartment() || $data->isGlobalPrivilege()

                ) &&
                $data->getName() === ReportDashboardPermission::FULL
             ) {
                $form->get('name')->addError(new FormError('can\'t grant edit permission for agent to default dashboard)'));
            }
        }
    }
}

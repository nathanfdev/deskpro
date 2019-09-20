<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Approval;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\Approval\SelectedApprovers;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Form\Type\EntityIdType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SelectedApproversType
 *
 * @package DeskPRO\Bundle\AppBundle\Form\Type\Approval
 */
class SelectedApproversType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('has_ticket_user', ApiBooleanType::class, [
                'required' => false,
            ])
            ->add('has_organization_managers', ApiBooleanType::class, [
                'required' => false,
            ])
            ->add('has_all_agents', ApiBooleanType::class, [
                'required' => false,
            ])
            ->add('people', EntityIdType::class, [
                'required' => false,
                'class' => Person::class,
                'multiple' => true,
                'keep_as_ids' => true,
                'constraints' => [
                    new Assert\Count(['min' => 1, 'groups' => ['mandate_people']]),
                ],
            ])
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SelectedApprovers::class,
            'csrf_protection' => false,
            'csrf_double_submit_protection' => false,
            'validation_groups' => function (FormInterface $form) {
                $hasTicketUser = $form->get('has_ticket_user')->getData();
                $hasOrganizationManagers = $form->get('has_organization_managers')->getData();
                $hasAllAgents = $form->get('has_all_agents')->getData();

                if ($hasTicketUser || $hasOrganizationManagers || $hasAllAgents) {
                    return ['Default'];
                }

                return ['Default', 'mandate_people'];
            },
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'selected_approvers';
    }
}

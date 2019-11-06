<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Approval;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\Approval\AbstractBaseApproval;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApproverSelectionCriteria;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Form\Type\EntityIdType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ApproverSelectionCriteriaType.
 */
class ApproverSelectionCriteriaType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('can_select_ticket_user', ApiBooleanType::class, [
                'required' => false,
            ])
            ->add('can_select_organization_managers', ApiBooleanType::class, [
                'required' => false,
            ])
            ->add('can_select_from_all_agents', ApiBooleanType::class, [
                'required' => false,
            ])
            ->add('select_from_people', EntityIdType::class, [
                'required'    => false,
                'class'       => Person::class,
                'multiple'    => true,
                'keep_as_ids' => true,
                'constraints' => [
                    new Assert\Count(['min' => 1, 'groups' => ['mandate_select_from_people']]),
                ],
            ])
            ->add('min_number_of_approvers', IntegerType::class, [
                'required'    => false,
                'constraints' => [
                    new Assert\NotBlank(['message' => sprintf('You must provide a minimum number of approvers value between 1 & %d', AbstractBaseApproval::APPROVERS_MAX)]),
                    new Assert\Range(['min' => 1, 'max' => AbstractBaseApproval::APPROVERS_MAX]),
                ],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'        => ApproverSelectionCriteria::class,
            'validation_groups' => function (FormInterface $form) {
                $canSelectTicketUser = $form->get('can_select_ticket_user')->getData();
                $canSelectOrganizationManagers = $form->get('can_select_organization_managers')->getData();
                $canSelectAllAgents = $form->get('can_select_from_all_agents')->getData();

                if ($canSelectTicketUser || $canSelectOrganizationManagers || $canSelectAllAgents) {
                    return ['Default'];
                }

                return ['Default', 'mandate_select_from_people'];
            },
        ]);
    }
}

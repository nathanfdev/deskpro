<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Approval;

use DeskPRO\Bundle\ApiBundle\Controller\Webhooks\TriggerActionsFormType;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalTemplate;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalType;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Validator\Constraints\Approval\ApproverCriteria;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ApprovalTemplateType
 *
 * @package DeskPRO\Bundle\AppBundle\Form\Type\Approval
 */
class ApprovalTemplateType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', EntityType::class, [
                'required' => true,
                'class' => ApprovalType::class,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 255]),
                ],
            ])
            ->add('description', TextType::class, [
                'required' => false,
            ])
            ->add('required_approvals', IntegerType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\GreaterThan(['value' => 0])
                ],
            ])
            ->add('required_rejections', IntegerType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\GreaterThan(['value' => 0])
                ],
            ])
            ->add('can_approvers_view_subject', ApiBooleanType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotNull(),
                ],
            ])
            ->add('approver_criteria', ApproverCriteriaType::class, [
                'by_reference' => false,
                'constraints' => [
                    new ApproverCriteria(),
                ],
            ])
            ->add('actions_on_create', TriggerActionsFormType::class, [
                'required' => false,
                'data_key' => null,
            ])
            ->add('actions_on_partial_approval_response', TriggerActionsFormType::class, [
                'required' => false,
                'data_key' => null,
            ])
            ->add('actions_on_partial_rejection_response', TriggerActionsFormType::class, [
                'required' => false,
                'data_key' => null,
            ])
            ->add('actions_on_cancel', TriggerActionsFormType::class, [
                'required' => false,
                'data_key' => null,
            ])
            ->add('actions_on_approved', TriggerActionsFormType::class, [
                'required' => false,
                'data_key' => null,
            ])
            ->add('actions_on_rejected', TriggerActionsFormType::class, [
                'required' => false,
                'data_key' => null,
            ])
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ApprovalTemplate::class,
            'csrf_protection' => false,
            'csrf_double_submit_protection' => false,
        ]);
    }
}

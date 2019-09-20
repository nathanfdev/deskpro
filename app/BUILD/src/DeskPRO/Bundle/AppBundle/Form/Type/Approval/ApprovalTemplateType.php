<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Approval;

use DeskPRO\Bundle\ApiBundle\Controller\Webhooks\TriggerActionsFormType;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalTemplate;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalType;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Validator\Constraints\Approval\ApprovalThresholds;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
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
                    new Assert\GreaterThanOrEqual(['value' => 0]),
                    new Assert\GreaterThan(['value' => 0, 'groups' => ['required_approvals_disallow_zero']]),
                ],
            ])
            ->add('required_rejections', IntegerType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\GreaterThanOrEqual(['value' => 0]),
                    new Assert\GreaterThan(['value' => 0, 'groups' => ['required_rejections_disallow_zero']]),
                ],
            ])
            ->add('can_approvers_view_subject', ApiBooleanType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotNull(),
                ],
            ])
            ->add('can_choose_approvers', ApiBooleanType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotNull(),
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

        $onCanChooseApprovers = function (FormInterface $form, $canChooseApprovers) {
            if ($canChooseApprovers) {
                $form
                    ->add('approver_selection_criteria', ApproverSelectionCriteriaType::class, [
                        'by_reference' => false,
                    ])
                ;
                if ($form->has('selected_approvers')) {
                    $form->remove('selected_approvers');
                }
            } else {
                $form
                    ->add('selected_approvers', SelectedApproversType::class, [
                        'by_reference' => false,
                    ])
                ;
                if ($form->has('approver_selection_criteria')) {
                    $form->remove('approver_selection_criteria');
                }
            }
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($onCanChooseApprovers) {
            $onCanChooseApprovers($event->getForm(), $event->getData()->canChooseApprovers());
        });

        $builder->get('can_choose_approvers')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($onCanChooseApprovers) {
            $onCanChooseApprovers($event->getForm()->getParent(), $event->getForm()->getData());
        });
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
            'validation_groups' => function (FormInterface $form) {
                if (!$form->get('required_approvals')->getData() && !$form->get('required_rejections')->getData()) {
                    return ['required_approvals_disallow_zero', 'required_rejections_disallow_zero', 'Default'];
                }

                return ['Default'];
            },
            'constraints' => [
                new ApprovalThresholds(),
            ],
        ]);
    }
}

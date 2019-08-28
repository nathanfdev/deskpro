<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Approval;

use DeskPRO\Bundle\AppBundle\Entity\Approval\AbstractBaseApproval;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalTemplate;
use DeskPRO\Bundle\AppBundle\Validator\Constraints\Approval\ApprovalThresholds;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ApprovalType
 *
 * @package DeskPRO\Bundle\AppBundle\Form\Type\Approval
 */
class BaseApprovalType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('template', EntityType::class, [
                'required' => true,
                'class' => ApprovalTemplate::class,
                'mapped' => false,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('approvers', CollectionType::class, [
                'required' => true,
                'entry_type' => IntegerType::class,
                'allow_add' => true,
                'allow_delete' => false,
                'by_reference' => false,
                'constraints' => [
                    new Assert\Count(['min' => 1, 'max' => AbstractBaseApproval::APPROVERS_MAX, 'groups' => ['can_choose_approvers']]),
                    new Assert\Count(['min' => 0, 'max' => 0, 'groups' => ['cannot_choose_approvers']]),
                ],
            ])
            ->add('description', TextType::class, [
                'required' => false,
            ])
        ;

        $builder->setEmptyData(function (FormInterface $form) use ($options) {
            /** @var ApprovalTemplate|null $template */
            if ($template = $form->get('template')->getData()) {
                return call_user_func(
                    [$options['data_class'], 'createFromTemplate'],
                    $template
                );
            }

            return null;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'csrf_double_submit_protection' => false,
            'validation_groups' => function (FormInterface $form) {
                /** @var ApprovalTemplate $template */
                $template = $form->get('template')->getData();

                if (!$template) {
                    return ['Default'];
                }

                return $template->getApproverCriteria()->canChooseApprovers()
                    ? ['can_choose_approvers', 'Default']
                    : ['cannot_choose_approvers', 'Default']
                ;
            },
            'constraints' => [
                new ApprovalThresholds(),
            ],
        ]);

        $resolver->setRequired([
            'data_class',
        ]);

        $resolver->setAllowedValues('data_class', function ($value) {
            return is_subclass_of($value, AbstractBaseApproval::class);
        });
    }
}

<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Approval;

use DeskPRO\Bundle\AppBundle\Entity\Approval\AbstractBaseApproval;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalTemplate;
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
                    new Assert\Count(['min' => 0, 'max' => AbstractBaseApproval::APPROVERS_MAX, 'groups' => ['if_criteria_contains_users']]),
                    new Assert\Count(['min' => 1, 'max' => AbstractBaseApproval::APPROVERS_MAX, 'groups' => ['if_criteria_doesnt_contains_users']]),
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
                if ($template = $form->get('template')->getData()) {
                    return $template->getApproverCriteria()->hasPeople()
                        ? ['Default', 'if_criteria_contains_users']
                        : ['Default', 'if_criteria_doesnt_contains_users']
                    ;
                }

                return ['Default'];
            }
        ]);

        $resolver->setRequired([
            'data_class',
        ]);

        $resolver->setAllowedValues('data_class', function ($value) {
            return is_subclass_of($value, AbstractBaseApproval::class);
        });
    }
}

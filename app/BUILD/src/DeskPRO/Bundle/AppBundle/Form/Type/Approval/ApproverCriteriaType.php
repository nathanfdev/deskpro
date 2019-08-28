<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Approval;

use DeskPRO\Bundle\AppBundle\Entity\Approval\ApproverCriteria;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ApproverCriteriaType
 *
 * @package DeskPRO\Bundle\AppBundle\Form\Type\Approval
 */
class ApproverCriteriaType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('can_choose_approvers', ApiBooleanType::class, [
                'required' => false,
            ])
            ->add('agents', CollectionType::class, [
                'required' => false,
                'entry_type' => IntegerType::class,
                'allow_add' => true,
                'allow_delete' => true,
            ])
            ->add('all_agents', ApiBooleanType::class, [
                'required' => false,
            ])
            ->add('users', CollectionType::class, [
                'required' => false,
                'entry_type' => IntegerType::class,
                'allow_add' => true,
                'allow_delete' => true,
            ])
            ->add('all_users', ApiBooleanType::class, [
                'required' => false,
            ])
            ->add('organization_managers', ApiBooleanType::class, [
                'required' => false,
            ])
            ->add('teams', CollectionType::class, [
                'required' => false,
                'entry_type' => IntegerType::class,
                'allow_add' => true,
                'allow_delete' => true,
            ])
            ->add('departments', CollectionType::class, [
                'required' => false,
                'entry_type' => IntegerType::class,
                'allow_add' => true,
                'allow_delete' => true,
            ])
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ApproverCriteria::class,
            'csrf_protection' => false,
            'csrf_double_submit_protection' => false,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'approver_criteria';
    }
}

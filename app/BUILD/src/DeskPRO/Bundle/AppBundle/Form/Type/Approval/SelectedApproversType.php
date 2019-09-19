<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Approval;

use DeskPRO\Bundle\AppBundle\Entity\Approval\SelectedApprovers;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ->add('people', CollectionType::class, [
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
            'data_class' => SelectedApprovers::class,
            'csrf_protection' => false,
            'csrf_double_submit_protection' => false,
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

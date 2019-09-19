<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Approval;

use DeskPRO\Bundle\AppBundle\Entity\Approval\ApproverSelectionCriteria;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ApproverSelectionCriteriaType
 *
 * @package DeskPRO\Bundle\AppBundle\Form\Type\Approval
 */
class ApproverSelectionCriteriaType extends AbstractType
{
    /**
     * {@inheritDoc}
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
            ->add('can_select_all_agents', ApiBooleanType::class, [
                'required' => false,
            ])
            ->add('select_from_people', CollectionType::class, [
                'required' => false,
                'entry_type' => IntegerType::class,
                'allow_add' => true,
                'allow_delete' => true,
            ])
            ->add('min_number_of_approvers', IntegerType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\GreaterThan(['value' => 0]),
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
            'data_class' => ApproverSelectionCriteria::class,
            'csrf_protection' => false,
            'csrf_double_submit_protection' => false,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'approver_selection_criteria';
    }
}

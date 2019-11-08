<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Approval;

use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalResponse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ApprovalResponseType
 *
 * @package DeskPRO\Bundle\AppBundle\Form\Type\Approval
 */
class ApprovalResponseType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('message', TextType::class, [
                'required' => false,
            ])
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ApprovalResponse::class,
        ]);
    }
}

<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\People;

use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PersonPasswordResetRequestType.
 */
class PersonPasswordResetRequestType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', EmailType::class, [
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\Email(['strict' => true]),
                new AppAssert\Person\Email\ExistEmail(),
                new AppAssert\Person\PersonType(['type' => 'agent']),
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection'               => false,
            'csrf_double_submit_protection' => false,
        ]);
    }
}

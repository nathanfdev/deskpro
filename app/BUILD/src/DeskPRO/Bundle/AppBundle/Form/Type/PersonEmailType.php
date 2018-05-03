<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use Application\DeskPRO\Entity\PersonEmail;
use DeskPRO\Bundle\AppBundle\Validator\Constraints\Person\Email\FreeEmail as FreeEmailConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PersonEmailType.
 */
class PersonEmailType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', EmailType::class, [
            'label'          => $options['email_label'],
            'required'       => $options['required'],
            'error_bubbling' => $options['inline'],
            'mapped'         => $options['mapped_email'],
            'constraints'    => $options['email_constraints'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'   => PersonEmail::class,
            'email_label'  => false,
            'inline'       => false,
            'mapped_email' => true,
            'constraints'  => [
                new FreeEmailConstraint(),
            ],
            'email_constraints' => [
                new Assert\NotBlank(),
                new Assert\Email(['strict' => true]),
            ],
        ]);
    }
}

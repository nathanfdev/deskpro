<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\People;

use Application\DeskPRO\Entity\Language;
use DeskPRO\Bundle\AppBundle\Form\Type\BlobAuthType;
use DeskPRO\Bundle\AppBundle\Form\Type\PhoneNumberType;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppConstraints;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

/**
 * Class PersonProfileType.
 */
class PersonProfileType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('display_name', TextType::class, [
                'property_path' => 'override_display_name',
            ])
            ->add('avatar_blob_auth', BlobAuthType::class, [
                'required'      => false,
                'property_path' => 'picture_blob',
            ])
            ->add('phone', PhoneNumberType::class, [
                'property_path'  => 'primaryPhoneNumber',
                'error_bubbling' => false,
                'required'       => false,
                'person'         => $builder->getData(),
                'constraints'    => [
                    new AppConstraints\PhoneNumber(),
                ],
            ])
            ->add('language_id', EntityType::class, [
                'class'         => Language::class,
                'property_path' => 'language',
            ])
            ->add('timezone', TextType::class)
            ->add('password', RepeatedType::class, [
                'type'            => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'required'        => false,
                'error_bubbling'  => false,
                'first_options'   => [
                    'error_bubbling' => true,
                ],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return BasePersonType::class;
    }
}

<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppConstraints;
use Orb\Util\Arrays;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
            ->add('name', TextType::class)
            ->add('display_name', TextType::class, [
                'property_path' => 'override_display_name',
            ])
            ->add('avatar_blob_auth_id', BlobAuthType::class, [
                'required'      => false,
                'property_path' => 'picture_blob',
            ])
            ->add('emails', CollectionType::class, [
                'type'            => EmailType::class,
                'allow_add'       => true,
                'allow_delete'    => true,
                'invalid_message' => 'Invalid Email.',
                'by_reference'    => true,
                'property_path'   => 'emailAddresses',
                'error_bubbling'  => false,
                'options'         => [
                    'error_bubbling' => true,
                ],
            ])
            ->add('primary_email', EmailType::class, [
                'property_path' => 'email',
            ])
            ->add('phone', PhoneNumberType::class, [
                'property_path'  => 'primaryPhoneNumber',
                'error_bubbling' => false,
                'required'       => false,
                'constraints'    => [
                    new AppConstraints\PhoneNumber(),
                ],
            ])
            ->add('language_id', EntityType::class, [
                'class'         => 'DeskPRO:Language',
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
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $data = $event->getData();
                if (!empty($data['emails'])) {
                    $data['emails'] = Arrays::removeFalsey($data['emails']);
                }

                $event->setData($data);
            })
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'error_mapping'   => [
                'emails' => 'emails',
            ],
            'constraints' => [
                new AppConstraints\Person\Email\FreeEmail([
                    'property' => 'emailAddresses',
                ]),
            ],
        ]);
    }
}

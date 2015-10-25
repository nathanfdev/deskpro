<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

use DeskPRO\Bundle\AppBundle\Validator\Constraints\FreeEmail;
use DeskPRO\Bundle\AppBundle\Validator\Constraints\NotSystemEmail;
use DeskPRO\Bundle\AppBundle\Validator\Constraints\PhoneNumber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class PersonProfileType.
 */
class PersonProfileType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'person_profile';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('display_name', 'text', [
                'property_path' => 'override_display_name',
            ])
            ->add('emails', 'collection', [
                'type'            => 'email',
                'allow_add'       => true,
                'allow_delete'    => true,
                'invalid_message' => 'Invalid Email.',
                'by_reference'    => true,
                'property_path'   => 'emailAddresses',
                'error_bubbling'  => false,
                'constraints'     => [
                    new NotSystemEmail(),
                ],
            ])
            ->add('primary_email', 'email', [
                'property_path' => 'email',
                'constraints'   => [
                    new Email(),
                    new NotSystemEmail(),
                ],
            ])
            ->add('phone', new PhoneNumberType(), [
                'property_path'  => 'primaryPhoneNumber',
                'error_bubbling' => false,
                'constraints'    => [
                    new PhoneNumber(),
                ],
            ])
            ->add('language_id', 'entity', [
                'class'         => 'DeskPRO:Language',
                'property_path' => 'language',
            ])
            ->add('timezone', 'text')
            ->add('password', 'repeated', [
                'type'            => 'password',
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
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'constraints'     => [
                new FreeEmail([
                    'property' => 'emailAddresses',
                ]),
            ],
        ]);
    }
}

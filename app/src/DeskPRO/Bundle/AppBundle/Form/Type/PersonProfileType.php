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

use DeskPRO\Bundle\AppBundle\Form\DataTransformer\BlobAuthTransformer;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppConstraints;
use Doctrine\ORM\EntityManager;
use Orb\Util\Arrays;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints;

/**
 * Class PersonProfileType.
 */
class PersonProfileType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

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
                    new Constraints\NotBlank(),
                ],
            ])
            ->add('display_name', 'text', [
                'property_path' => 'override_display_name',
            ])
            ->add('avatar_blob_auth_id', 'text', [
                'required'      => false,
                'property_path' => 'picture_blob',
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
                    new Constraints\All([
                        new Constraints\Email(),
                        new AppConstraints\NotSystemEmail(),
                        new AppConstraints\NotBannedEmail(),
                    ]),
                ],
                'options' => [
                    'error_bubbling' => true,
                ],
            ])
            ->add('primary_email', 'email', [
                'property_path' => 'email',
                'constraints'   => [
                    new Constraints\Email(),
                    new AppConstraints\NotSystemEmail(),
                    new AppConstraints\NotBannedEmail(),
                ],
            ])
            ->add('phone', new PhoneNumberType(), [
                'property_path'  => 'primaryPhoneNumber',
                'error_bubbling' => false,
                'required'       => false,
                'constraints'    => [
                    new AppConstraints\PhoneNumber(),
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
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $data = $event->getData();
                $data['emails'] = Arrays::removeFalsey($data['emails']);

                $event->setData($data);
            })
        ;

        $builder
            ->get('avatar_blob_auth_id')
            ->addModelTransformer(new BlobAuthTransformer($this->em))
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
                new AppConstraints\FreeEmail([
                    'property' => 'emailAddresses',
                ]),
            ],
        ]);
    }
}

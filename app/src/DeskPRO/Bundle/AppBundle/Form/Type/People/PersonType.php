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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\Form\Type\People;

use Application\DeskPRO\Entity\LabelPerson;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiType;
use DeskPRO\Bundle\AppBundle\Form\Type\People\PersonEmail\PersonEmailType;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface as Context;

/**
 * Class PersonType.
 */
class PersonType extends ApiType
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * PersonType constructor.
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
        return 'api_person';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Person $person */
        $person = $builder->getData();

        $builder
            ->add('name', 'text')
            ->add('first_name', 'text')
            ->add('last_name', 'text')
            ->add('summary', 'text')
            ->add('organization', 'entity', ['class' => 'DeskPRO:Organization'])
            ->add('language', 'entity', ['class' => 'DeskPRO:Language'])
            ->add('labels', 'api_labels_collection', [
                'labels_class'   => LabelPerson::class,
                'labels_owner'   => $builder->getData(),
                'owner_property' => 'person',
            ])
            ->add('primary_email', new PersonEmailType($person, $this->em), ['mapped' => false])
            ->add('emails', 'collection', [
                'type'         => new PersonEmailType($person, $this->em),
                'mapped'       => false,
                'allow_add'    => true,
                'allow_delete' => true,
                'constraints'  => [

                    // Don't allow to remove all emails
                    new Constraints\Callback(function ($data, Context $context) use ($person) {
                        $hasEmails = count($person->getEmailAddresses()) > 0;
                        $submittedPrimaryEmail = !is_null($context->getRoot()->get('primary_email')->getData());
                        $submittedSecondaryEmails = count($data) > 0;
                        $submittedAnyEmails = $submittedPrimaryEmail || $submittedSecondaryEmails;
                        if ($hasEmails && !$submittedAnyEmails) {
                            $context->addViolation('You can not remove all emails');
                        }
                    }),
                ],
            ])
        ;

        // Normalize user input: copy primary_email into emails provided and it doesn't contain the primary email
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            if (array_key_exists('primary_email', $data) && array_key_exists('emails', $data)) {
                if (!in_array($data['primary_email'], $data['emails'])) {
                    $data['emails'][] = $data['primary_email'];
                    $event->setData($data);
                }
            }
        });

        // Email fields are not mapped to bypass calling setters with side effects on the Person entity (need to bypass
        // setting the person value to the passed $email, which bothers validation)
        //
        // setting primary_email and emails manually on after validation happened
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($person) {
            $emails_form = $event->getForm()->get('emails');
            if ($emails_form->isSubmitted()) {
                $person->setEmails($emails_form->getData());
            }
            $primary_email_form = $event->getForm()->get('primary_email');
            if ($primary_email_form->isSubmitted()) {
                $person->setPrimaryEmail($primary_email_form->getData());
            }
        });
    }
}

<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Form\Error\ErrorsCodes;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Assigns person entity using multiple formats.
 * Form can accept person "id", "email address" or {"email": "xxx", "name": "xxx"}.
 */
class PersonAssignType extends AbstractType
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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', TextType::class, [
                'required' => false,
                'mapped'   => false,
            ])
            ->add('name', TextType::class, [
                'label'    => $options['label_name'],
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'label'       => $options['label_email'],
                'required'    => false,
                'mapped'      => false,
                'constraints' => [
                    new Assert\Email(['strict' => true]),
                ],
            ])
        ;

        foreach (['id', 'name', 'email'] as $available_field) {
            if (!in_array($available_field, $options['available_fields'])) {
                $builder->remove($available_field);
            }
        }

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onSetFields'], 200);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onSetPerson'], 100);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onResetPerson']);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onSetFields(FormEvent $event)
    {
        $data = $event->getData();

        if (is_scalar($data)) {
            if (is_numeric($data)) {
                $event->setData(['id' => $data]);
            } else {
                $event->setData(['email' => $data]);
            }
        }
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onSetPerson(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        /** @var \Application\DeskPRO\EntityRepository\Person $personRepository */
        $personRepository = $this->em->getRepository(Person::class);
        $defaultPerson    = $form->getConfig()->getOption('person');

        // Set person entity from request fields (id or email)
        // Creates a new person if no person found by provided email

        if (!empty($data['email'])) {
            $person = $personRepository->findOneByEmail($data['email']);
            if (!$person) {
                $allowCreate = $form->getConfig()->getOption('allow_create');
                if ($allowCreate) {
                    $person = new Person();
                    $person->addEmailAddressString($data['email']);
                } else {
                    $form->addError(new FormError(ErrorsCodes::NO_PERSON, null, ['value' => $data['email']]));
                }
            }

            $form->setData($person);
        } elseif (!empty($data['id'])) {
            /** @var Person $person */
            $person = $personRepository->find((int) $data['id']);
            if (!$person) {
                $form->addError(new FormError(ErrorsCodes::NO_PERSON, null, ['value' => $data['id']]));
            }

            $form->setData($person);

        // Use config person entity as default
        } elseif (!$form->getData() && $defaultPerson) {
            $form->setData($defaultPerson);
        }

        /** @var Person $person */
        $person = $form->getData();
        if (!isset($data['name']) && $person) {
            // If we sent just ID and person doesn't have name
            // then force set name from its display name to prevent validation error as name is required field
            if ($person->getId() && !$person->getName()) {
                $person->setName($person->getDisplayName());
            }

            $data['name'] = $person->getName();

            $event->setData($data);
        }
    }

    /**
     * We should set person to NULL if it was not found or not created properly.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onResetPerson(FormEvent $event)
    {
        $data = $event->getData();
        if ($data instanceof Person && !$data->getEmailAddress()) {
            $event->setData(null);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'person'             => null,
                'label_name'         => '',
                'label_email'        => '',
                'data_class'         => Person::class,
                'available_fields'   => ['id', 'name', 'email'],
                'allow_extra_fields' => false,
                'allow_create'       => false,
                'error_bubbling'     => false,
            ])
            ->setAllowedTypes([
                'person'       => ['null', Person::class],
                'allow_create' => 'boolean',
            ])
        ;
    }
}

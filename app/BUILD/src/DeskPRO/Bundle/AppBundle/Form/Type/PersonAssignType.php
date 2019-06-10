<?php

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

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit'], 100);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmit']);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (is_scalar($data)) {
            if (is_numeric($data)) {
                $data = ['id' => $data];
            } else {
                $data = ['email' => $data];
            }

            $event->setData($data);
        }

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
            // If we sent just ID or email and person doesn't have a name
            // then force set name from its display name to prevent validation error because name is a required field
            if (!$person->getName()) {
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
    public function onSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if ($data instanceof Person
            // should be at least phone number or email
            && !$data->getEmailAddress()
            && !$data->getPhoneNumbers()->count() > 0
        ) {
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
                'allow_extra_fields' => false,
                'allow_create'       => false,
                'error_bubbling'     => false,
                'error_mapping'      => [
                    'emails' => 'email',
                ],
            ])
            ->setAllowedTypes('person', ['null', Person::class])
            ->setAllowedTypes('allow_create', 'boolean')
        ;
    }
}

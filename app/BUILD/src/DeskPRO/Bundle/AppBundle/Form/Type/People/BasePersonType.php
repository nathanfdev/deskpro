<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\People;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Form\Type\People\PersonEmail\PersonEmailType;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Class BasePersonType.
 */
class BasePersonType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var bool
     */
    private $useUniqueEmail;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     * @param TokenStorage $tokenStorage
     * @param bool $useUniqueEmail
     */
    public function __construct(EntityManager $em, TokenStorage $tokenStorage, $useUniqueEmail)
    {
        $this->em             = $em;
        $this->tokenStorage   = $tokenStorage;
        $this->useUniqueEmail = $useUniqueEmail;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
        ]);
    }

    /**
     * Sync `emails` and `primary email` props.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        /** @var Person $person */
        $form   = $event->getForm();
        $person = $form->getData();
        $data   = $event->getData();

        /** @var \Application\DeskPRO\Entity\Person $user */
        $token         = $this->tokenStorage->getToken();
        $sessionPerson = $token ? $token->getUser() : null;

        if ($sessionPerson instanceof Person
            && $person instanceof Person
            && (!$person->isAgent() || $sessionPerson->isAdmin() || $sessionPerson === $person)
        ) {
            $form
                ->add('name', TextType::class)
                ->add('primary_email', new PersonEmailType($person, $this->em, $this->useUniqueEmail))
                ->add('emails', CollectionType::class, [
                    'type'           => new PersonEmailType($person, $this->em, $this->useUniqueEmail),
                    'allow_add'      => true,
                    'allow_delete'   => true,
                    'delete_empty'   => true,
                    'by_reference'   => false,
                    'error_bubbling' => false,
                ])
            ;
        }

        if (!empty($data['primary_email'])) {
            if (!isset($data['emails'])) {
                $data['emails'] = $person->getEmailAddresses();
            }
            if (!in_array($data['primary_email'], $data['emails'])) {
                $data['emails'][] = $data['primary_email'];
            }
        } else {
            if (!empty($data['emails'])) {
                $data['primary_email'] = $data['emails'][0];
            } elseif (isset($data['emails'])) {
                $data['primary_email'] = '';
            }
        }

        $event->setData($data);
    }
}

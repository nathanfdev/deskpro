<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\People\PersonEmail;

use Application\DeskPRO\Entity\Person;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PersonEmailType.
 *
 * Handles email string as person primary email
 */
class PersonEmailType extends AbstractType
{
    /**
     * @var Person
     */
    private $person;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @param Person        $person
     * @param EntityManager $em
     */
    public function __construct(Person $person, EntityManager $em)
    {
        $this->person = $person;
        $this->em     = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new PersonEmailTransformer($this->person, $this->em));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'compound'    => false,
            'constraints' => [
                new PersonEmailConstraint($this->person, $this->em),
            ],
        ]);
    }
}

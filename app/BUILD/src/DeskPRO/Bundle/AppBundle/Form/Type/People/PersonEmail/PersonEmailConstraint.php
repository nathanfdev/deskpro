<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Form\Type\People\PersonEmail;

use Application\DeskPRO\Entity\Person;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;

/**
 * Class PersonEmailConstraint.
 */
class PersonEmailConstraint extends Constraint
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
     * PersonPersonEmailConstraint constructor.
     *
     * @param Person        $person
     * @param EntityManager $em
     */
    public function __construct(Person $person, EntityManager $em)
    {
        $this->person = $person;
        $this->em     = $em;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @return EntityManager
     */
    public function getEm()
    {
        return $this->em;
    }
}

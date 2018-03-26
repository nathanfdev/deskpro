<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Form\Type\People\PersonEmail;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class PersonEmailTransformer.
 */
class PersonEmailTransformer implements DataTransformerInterface
{
    /**
     * @var array [$person_obj_hash => ['email@test.com' =>]] Stores new email instances,
     *            to map equal emails to the same objects
     */
    private static $instances = [];

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
    public function transform($value)
    {
        if (is_null($value)) {
            return $value;
        }

        return $value->getEmail();
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (!$value) {
            return;
        }

        $person_hash = spl_object_hash($this->person);

        array_key_exists($person_hash, self::$instances) or self::$instances[$person_hash] = [];
        if (array_key_exists($value, self::$instances[$person_hash])) {
            return self::$instances[$person_hash][$value];
        }

        if (!$email = $this->em->getRepository(PersonEmail::class)->findOneBy(['email' => $value])) {
            $email = new PersonEmail();
            $email->setEmail($value);
            $email->setPerson($this->person);
            $this->person->addEmail($email);

            self::$instances[$person_hash][$value] = $email;
        }

        return $email;
    }
}

<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Sms\Detector;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PhoneNumber;
use Doctrine\ORM\EntityManager;

class PersonDetector
{
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     *  use number to find a person that sent us an sms.
     *
     * @param string $from_number
     *
     * @return \Application\DeskPRO\Entity\Person|null
     */
    public function detectWithFromNumber($from_number = null)
    {
        return $this->em->getRepository('DeskPRO:Person')->findOneByPhoneNumber($from_number);
    }

    /**
     * creates a person with the given phone number.
     *
     * @param string $from_number
     *
     * @throws \Doctrine\DBAL\ConnectionException
     *
     * @return Person
     */
    public function createPersonWithNumber($from_number)
    {
        $this->em->getConnection()->beginTransaction();

        $person = $this->detectWithFromNumber($from_number);

        if ($person) {
            $this->em->getConnection()->commit();

            return $person;
        }

        $person                  = Person::newContactPerson();
        $person->creation_system = 'gateway.person';
        $from_number             = new PhoneNumber($from_number);
        $person->setPrimaryPhoneNumber($from_number);

        $this->em->persist($person);
        $this->em->persist($from_number);
        $this->em->flush();

        $this->em->getConnection()->commit();

        return $person;
    }
}

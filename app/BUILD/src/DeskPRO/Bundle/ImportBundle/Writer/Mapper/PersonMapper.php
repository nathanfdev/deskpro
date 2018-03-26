<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

use Application\DeskPRO\Entity\Person;

/**
 * Person record mapper.
 *
 * Class Person
 */
class PersonMapper extends AbstractContainerMapper
{
    /**
     * {@inheritdoc}
     */
    public static function getMapperEntityClass()
    {
        return Person::class;
    }

    /**
     * Returns the existing person by email.
     *
     * @param string $email
     *
     * @return \Application\DeskPRO\Entity\Person
     */
    public function findOneByEmail($email)
    {
        return $this->getPersonRepository()->findOneByEmail($email);
    }

    /**
     * Returns the existing person by list of emails.
     *
     * @param array $emails
     *
     * @return \Application\DeskPRO\Entity\Person
     */
    public function findOneByEmails(array $emails)
    {
        $entities = $this->getPersonRepository()->findByEmails($emails);

        return array_shift($entities);
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\Person
     */
    protected function getPersonRepository()
    {
        return $this->em->getRepository($this->getEntityClass());
    }
}

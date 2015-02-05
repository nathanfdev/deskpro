<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\ImportBundle\Generator\Writer\DeskPro\Importer\Mapper;

use Application\DeskPRO\EntityRepository;

/**
 * Person record mapper
 *
 * Class Person
 * @package Application\ImportBundle\Generator\Writer\DeskPro\Importer\Mapper
 */
final class Person implements MapperInterface
{
    /**
     * @var EntityRepository\Person
     */
    private $repository;

    /**
     * Constructor
     *
     * @param EntityRepository\Person $repository
     */
    public function __construct(EntityRepository\Person $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE_PERSON;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria, $throw_exception = true)
    {
        $record = $this->repository->findOneBy($criteria);
        if (!$record && $throw_exception) {
            throw new MapperException('Person `%s` not found', $criteria);
        }

        return $record;
    }

    /**
     * Returns the existing person by email
     *
     * @param string $email
     * @param bool   $throw_exception
     *
     * @return \Application\DeskPRO\Entity\Person
     * @throws MapperException
     */
    public function findOneByEmail($email, $throw_exception = true)
    {
        $record = $this->repository->findOneByEmail($email);
        if (!$record && $throw_exception) {
            throw new MapperException('Person not found', array('email' => $email));
        }

        return $record;
    }

    /**
     * Returns the existing person by list of emails
     *
     * @param array $emails
     * @param bool  $throw_exception
     *
     * @return \Application\DeskPRO\Entity\Person
     * @throws MapperException
     */
    public function findOneByEmails(array $emails, $throw_exception = true)
    {
        $records = $this->repository->findByEmails($emails);
        if (empty($records) && $throw_exception) {
            throw new MapperException('Person not found', array('email' => $emails));
        }

        return array_shift($records);
    }
}

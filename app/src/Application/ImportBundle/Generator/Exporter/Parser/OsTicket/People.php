<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

namespace Application\ImportBundle\Generator\Exporter\Parser\OsTicket;

use Application\ImportBundle\Generator\GeneratorInterface;
use Application\ImportBundle\Entity;
use DateTime;
use Exception;

/**
 * OsTicket people parser
 *
 * Class People
 * @package Application\ImportBundle\Generator\Exporter\Parser\OsTicket
 */
class People extends AbstractParser
{
    /**
     * {@inheritdoc}
     */
    public function getRecordType()
    {
        return GeneratorInterface::RECORD_TYPE_PERSON;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        return $this->reader->getPeopleCount();
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        if (!($this->config->getBatchSize() > 0)) {
            throw new Exception('Invalid batch size set up');
        }

        $collection = new Entity\Collection();
        $collection
            ->merge($this->exportStaff())
            ->merge($this->exportUsers());

        foreach ($collection as $num => $person) {
            /** @var Entity\Person $person */
            $person
                ->setDestination('person_' . $num)
                ->setOid($num);
        }

        return $collection;
    }

    /**
     * Return a collection of staff
     *
     * @return Entity\Collection
     */
    private function exportStaff()
    {
        $offset     = 0;
        $collection = new Entity\Collection();

        while ($batch = $this->reader->findStaff($this->config->getBatchSize(), $offset)) {
            $offset += count($batch);

            foreach ($batch as $num => $person) {
                $this->advanceProgressBar();

                if ($this->hasRequiredStaffColumns($person) === false) {
                    $this->logWarning(sprintf('Invalid staff person record found (Skipping): %d', $num + $offset));
                } else {
                    $entity = new Entity\Person();
                    $entity
                        ->setDestination('person_' . ($num + $offset))
                        ->setAsAgent(true)
                        ->setName($person['firstname'] . $person['lastname'])
                        ->setFirstName($person['firstname'])
                        ->setLastName($person['lastname'])
                        ->setTimezone($this->reader->findTimezoneById($person['timezone_id']))
                        ->setDateCreated(new DateTime($person['created']))
                        ->addEmail($person['email']);

                    $collection->attach($entity);
                    $this->logInfo(sprintf('Staff `%s` parsed successfully!', $entity->getDestination()));
                }
            }
        }

        return $collection;
    }

    /**
     * Return a collection of users
     *
     * @return Entity\Collection
     */
    private function exportUsers()
    {
        $offset     = 0;
        $collection = new Entity\Collection();

        while ($batch = $this->reader->findUsers($this->config->getBatchSize(), $offset)) {
            $offset += count($batch);

            foreach ($batch as $num => $person) {
                $this->advanceProgressBar();

                if ($this->hasRequiredUserColumns($person) === false) {
                    $this->logWarning(sprintf('Invalid user record found (Skipping): %d', $num + $offset));
                } else {
                    $entity = new Entity\Person();
                    $entity
                        ->setDestination('person_' . ($num + $offset))
                        ->setAsUser(true)
                        ->setName($person['name'])
                        ->setDateCreated(new DateTime($person['created']))
                        ->addEmail($person['address']);

                    $collection->attach($entity);
                    $this->logInfo(sprintf('User `%s` parsed successfully!', $entity->getDestination()));
                }
            }
        }

        return $collection;
    }

    /**
     * Check if staff person has all required columns
     *
     * @param array $person
     * @return bool
     */
    private function hasRequiredStaffColumns(array $person)
    {
        return $this->hasRequiredColumns($person, array('firstname', 'lastname', 'timezone_id', 'created', 'email'));
    }

    /**
     * Check if user has all required columns
     *
     * @param array $person
     * @return bool
     */
    private function hasRequiredUserColumns(array $person)
    {
        return $this->hasRequiredColumns($person, array('name', 'created', 'address'));
    }
}

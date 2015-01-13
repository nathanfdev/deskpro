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

/**
 * People os ticket parser
 *
 * Class People
 * @package Application\ImportBundle\Generator\Exporter\Parser\OsTicket
 */
class People extends AbstractOsTicket
{
    /**
     * {@inheritdoc}
     */
    public function getGeneratorRecordType()
    {
        return GeneratorInterface::TYPE_PEOPLE;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        return $this->os_ticket_reader->getPeopleCount();
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $collection = new Entity\Collection();
        $collection
            ->merge($this->exportStaff())
            ->merge($this->exportUsers());

        foreach ($collection as $num => $person) {
            /** @var Entity\Person $person */
            $person
                ->setDestination('person_' . $num)
                ->setOid($num);

            $collection->attach($person);
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

        while ($batch = $this->os_ticket_reader->findAllStaff($this->config->getBatchSize(), $offset)) {
            $offset += count($batch);

            foreach ($batch as $person) {
                $this->advanceProgressBar();

                $entity = new Entity\Person();
                $entity
                    ->setAsAgent(true)
                    ->setFirstName($person['firstname'])
                    ->setLastName($person['lastname'])
                    ->setTimezone($this->os_ticket_reader->findTimezoneFromId($person['timezone_id']))
                    ->setDateCreated(new DateTime($person['created']))
                    ->addEmail($person['email']);

                $collection->attach($entity);
                $this->logInfo(sprintf(
                    'Person `%s %s` parsed successfully!',
                    $entity->getFirstName(), $entity->getLastName()
                ));
            }
        }
    }

    /**
     * Return a collection of users
     * todo batch support?
     *
     * @return Entity\Collection
     */
    private function exportUsers()
    {
        $collection = new Entity\Collection();
        $users = $this->os_ticket_reader->findAllUsers($this->config->getBatchSize(), 0);
        foreach ($users as $person) {
            $this->advanceProgressBar();

            $entity = new Entity\Person();
            $entity
                ->setAsUser(true)
                ->setName($person['name'])
                ->setDateCreated(new DateTime($person['created']))
                ->addEmail($person['address']);

            $collection->attach($entity);
            $this->logInfo(sprintf(
                'Person `%s` parsed successfully!',
                $entity->getName()
            ));
        }

        return $collection;
    }
}

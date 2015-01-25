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

namespace Application\ImportBundle\Generator\Exporter\Parser\Json;

use Application\ImportBundle\Generator\GeneratorInterface;
use Application\ImportBundle\Generator\Writer\Json\Destination;
use Application\ImportBundle\Entity;
use DateTime;

/**
 * People json file parser
 *
 * Class People
 * @package Application\ImportBundle\Generator\Exporter\Parser\Json
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
        return $this->reader->getDirectoryFilesCount($this->getConfig());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $collection = new Entity\Collection();
        $people = $this->reader->getData($this->getConfig());
        foreach ($people as $num => $person) {
            $this->advanceProgressBar();

            if ($this->hasRequiredPersonColumns($person) === false) {
                $this->logWarning(sprintf('Invalid person record found (Skipping): %d', $num));
            } else {
                $entity = new Entity\Person();
                $entity
                    ->setDestination('ticket_' . $person['oid'])
                    ->setOid($person['oid'])
                    ->setAsAgent($person['is_agent'])
                    ->setAsUser($person['is_user'])
                    ->setFirstName($person['first_name'])
                    ->setLastName($person['last_name'])
                    ->setName($person['name'])
                    ->setTimezone($person['timezone'])
                    ->setDateCreated(new DateTime($person['date_created']));

                foreach ($person['emails'] as $email) {
                    $entity->addEmail($email);
                }

                $collection->attach($entity);
                $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));
            }
        }

        return $collection;
    }

    /**
     * Returns record type reader config
     *
     * @return \Application\ImportBundle\JsonReader\JsonConfig
     */
    private function getConfig()
    {
        return $this->getReaderConfig(Destination\DestinationInterface::ENTITY_PERSON_PATH);
    }

    /**
     * Check if person has all required columns
     *
     * @param array $person
     * @return bool
     */
    private function hasRequiredPersonColumns(array $person)
    {
        $columns = array(
            'oid',
            'is_agent',
            'is_user',
            'first_name',
            'last_name',
            'name',
            'timezone',
            'date_created',
            'emails',
        );

        return $this->hasRequiredColumns($person, $columns) && is_array($person['emails']);
    }
}

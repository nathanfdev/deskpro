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

namespace Application\ImportBundle\Generator\Exporter\Parser\Csv;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Parser\NoColumnException;
use DateTime;

/**
 * People csv file parser
 *
 * Class People
 * @package Application\ImportBundle\Generator\Exporter\Parser\Csv
 */
final class People extends AbstractParser
{
    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_PERSON;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        return $this->getReaderCount($this->getConfig());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $collection = new Entity\Collection();
        $people     = $this->getReaderData($this->getConfig());

        foreach ($people as $num => $person) {
            $this->advanceProgressBar();

            try {
                $entity = $this->exportPerson($num, $person);
                if ($entity) {
                    $collection->attach($entity);
                    $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));
                } else {
                    $this->logError(sprintf('Invalid person record `%d` found (Skipping)', $num));
                }

            } catch (NoColumnException $e) {
                $this->logError(sprintf(
                    'Invalid person record `%d` found (Skipping): %s',
                    $num, $e->getMessage()
                ));
            }
        }

        return $collection;
    }

    /**
     * Returns a person entity
     *
     * @param int   $num
     * @param array $person
     *
     * @return Entity\Person|null
     */
    private function exportPerson($num, array $person)
    {
        if ($this->isValidPerson($person)) {
            $entity = new Entity\Person();
            $entity
                ->setDestination('person_' . $num)
                ->setOid($num)
                ->setAsAgent($this->isAgent($person))
                ->setName($person['name'])
                ->setDateCreated(new DateTime())
                ->addEmail($person['email']);

            return $entity;
        }

        return null;
    }

    /**
     * Check if person has all required columns
     *
     * @param array $person
     * @return bool
     */
    private function isValidPerson(array $person)
    {
        $columns = array('name', 'email');
        return $this->hasRequiredColumns($person, $columns);
    }

    /**
     * Returns record type reader config
     *
     * @return \Application\ImportBundle\Reader\Csv\CsvConfig
     */
    private function getConfig()
    {
        return $this->getReaderConfig(self::FILE_PEOPLE);
    }

    /**
     * Check if person is agent
     *
     * @param array $person
     * @return bool
     */
    private function isAgent(array $person)
    {
        return isset($person['is_agent']) && $this->isBooleanTrue($person['is_agent']);
    }
}

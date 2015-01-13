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

use Application\ImportBundle\Generator\GeneratorInterface;
use Application\ImportBundle\Entity;
use DateTime;

/**
 * People csv file parser
 *
 * Class People
 * @package Application\ImportBundle\Generator\Exporter\Parser\Csv
 */
class People extends AbstractCsv
{
    const FILE_PEOPLE = 'people.csv';

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
        return $this->csv_reader->getRowsCount($this->getCsvReaderConfig(self::FILE_PEOPLE));
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $collection = new Entity\Collection();
        $people = $this->csv_reader->getData($this->getCsvReaderConfig(self::FILE_PEOPLE));
        foreach ($people as $num => $person) {
            $this->advanceProgressBar();

            if ($this->hasRequiredPersonColumns($person) === false) {
                $this->logWarning(sprintf('Invalid person record found (Skipping): %d', $num));
            } else {
                if (empty($person['name'])) {
                    $e = explode('@', $person['email'], 2);
                    $person['name'] = $e[0];
                }

                $names  = explode(' ', $person['name']);
                $entity = new Entity\Person();
                $entity
                    ->setDestination('person_' . $num)
                    ->setOid($num)
                    ->setAsAgent(isset($person['is_agent']) ? (bool)$person['is_agent'] : false)
                    ->setName($person['name'])
                    ->setFirstName($names[0])
                    ->setLastName(isset($names[1]) ? $names[1] : '')
                    ->setDateCreated(new DateTime())
                    ->addEmail($person['email']);

                $collection->attach($entity);
                $this->logInfo(sprintf('%s parsed successfully!', $entity->getDestination()));
            }
        }

        return $collection;
    }

    /**
     * Check if person has all required columns
     *
     * @param array $person
     * @return bool
     */
    private function hasRequiredPersonColumns(array $person)
    {
        return $this->hasRequiredColumns($person, array('name', 'email'));
    }
}

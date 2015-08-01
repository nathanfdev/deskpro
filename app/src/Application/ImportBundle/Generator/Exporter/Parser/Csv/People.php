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
    const PERSON_PREFIX = 'person_';

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
        return $this->getReaderCount($this->getPersonReaderConfig());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $collection    = new Entity\Collection();

        $people        = $this->getReaderData($this->getPersonReaderConfig());
        $custom_fields = $this->exportPersonCustomFields();

        foreach ($people as $num => $person) {
            $this->advanceProgressBar();

            try {
                $entity = $this->exportPerson($person);
                if ($entity) {
                    foreach ($custom_fields as $custom_field_entity) {
                        /** @var Entity\CustomField $custom_field_entity */
                        if ($entity->getDestination() === $custom_field_entity->getDestination()) {
                            $entity->addCustomField($custom_field_entity);
                        }
                    }

                    $inline_custom_fields = $this->exportInlineCustomFields($entity->getDestination(), $person);
                    foreach ($inline_custom_fields as $custom_field_entity) {
                        $entity->addCustomField($custom_field_entity);
                    }

                    $collection->attach($entity);
                    $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));
                } else {
                    $this->logWarning(sprintf('Invalid person record `%d` found (Skipping)', $num));
                }

            } catch (NoColumnException $e) {
                $this->logWarning(sprintf(
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
     * @param array $person
     * @return Entity\Person|null
     */
    private function exportPerson(array $person)
    {
        if ($this->isPersonValid($person)) {
            $person_id = $this->getPersonId($person);
            $entity    = new Entity\Person();
            $entity
                ->setRawData($person)
                ->setDestination($this->formatDestination(self::PERSON_PREFIX, $person_id))
                ->setOid($person_id)
                ->setAsAgent($this->isAgent($person))
                ->setName($person['name'])
                ->setDateCreated(new DateTime())
                ->addEmail($person['email'])
            ;

            return $entity;
        }

        return null;
    }

    /**
     * Returns a collection of people custom field data
     *
     * @return Entity\Collection
     */
    private function exportPersonCustomFields()
    {
        return $this->exportCustomFields($this->getPersonCustomFieldReaderConfig(), self::PERSON_PREFIX, 'person_id');
    }

    /**
     * Check if person has all required columns
     *
     * @param array $person
     * @return bool
     */
    private function isPersonValid(array $person)
    {
        $columns = array(
            'name',
            'email',
        );

        return $this->hasRequiredColumns($person, $columns);
    }

    /**
     * Returns record type reader config
     *
     * @return \Application\ImportBundle\Reader\Csv\CsvConfig
     */
    private function getPersonReaderConfig()
    {
        return $this->getReaderConfig(self::FILE_PEOPLE);
    }

    /**
     * Returns reader config for people custom field records
     *
     * @return \Application\ImportBundle\Reader\Csv\CsvConfig
     */
    private function getPersonCustomFieldReaderConfig()
    {
        return $this->getReaderConfig(self::FILE_PEOPLE_CUSTOM_FIELDS);
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

    /**
     * Person id could be get from id or email column
     *
     * @param array $person
     * @return int|string
     */
    private function getPersonId(array $person)
    {
        return isset($person['id']) ? $person['id'] : $person['email'];
    }
}

<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace Application\ImportBundle\Generator\Exporter\Parser\Csv;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Generator\Exporter\Parser\ExportCollectionConfig;
use Application\ImportBundle\Reader\Csv\CsvReaderInterface;

/**
 * People csv file parser.
 *
 * Class People
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
        return $this->getReaderCount(CsvReaderInterface::FILE_PEOPLE);
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($this->getReaderData(CsvReaderInterface::FILE_PEOPLE))
            ->setPrefix('CSVPerson')
            ->setRefColumn('email')
            ->setMethod('exportPerson')
            ->setAdvanceProgressbar(true)
        ;

        $collection    = $this->exportCollection($config);
        $contact_data  = $this->exportPersonContactData();
        $custom_fields = $this->exportPersonCustomFields();

        foreach ($collection as $person) {
            /* @var Entity\Person $person */
            foreach ($contact_data as $contact) {
                if ($person->getDestination() === $contact->getDestination()) {
                    $person->addContact($contact);
                }
            }
            foreach ($custom_fields as $custom_field_entity) {
                if ($person->getDestination() === $custom_field_entity->getDestination()) {
                    $person->addCustomField($custom_field_entity);
                }
            }

            $inline_contact_data = $this->getInlineContactDataParser()->export($person->getRawData(), $person->getDestination());
            foreach ($inline_contact_data as $contact) {
                $person->addContact($contact);
            }

            $inline_custom_fields = $this->getInlineCustomFieldsParser()->export($person->getDestination(), $person->getRawData());
            foreach ($inline_custom_fields as $custom_field_entity) {
                $person->addCustomField($custom_field_entity);
            }
        }

        return $collection;
    }

    /**
     * Returns a person entity.
     *
     * @param array $person
     * @param int   $num
     *
     * @return Entity\Person
     */
    protected function exportPerson(array $person, $num)
    {
        $formatted = $this->formatter->format($person, array(
            'id'          => TransformerConfiguration::create(TransformerInterface::TYPE_STRING, array(
                'default' => 'num_'.$num,
            )),
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix'  => self::PERSON_PREFIX,
                'ref'     => array('original#id', 'email'),
            )),
            'name'         => TransformerInterface::TYPE_STRING,
            'email'        => TransformerInterface::TYPE_STRING,
            'date_created' => TransformerInterface::TYPE_DATE,
            'is_agent'     => TransformerInterface::TYPE_BOOLEAN,
        ));

            $entity = new Entity\Person();
            $entity
            ->setRawData($person)
            ->setDestination($formatted['destination'])
            ->setOid($formatted['id'])
            ->setAsUser(true)
            ->setAsAgent($formatted['is_agent'])
            ->setName($formatted['name'])
            ->setDateCreated($formatted['date_created'])
            ->addEmail($formatted['email'])
        ;

            return $entity;
        }

    /**
     * Returns a collection of people custom field data.
     *
     * @return Entity\CustomField[]|Entity\Collection
     */
    private function exportPersonCustomFields()
    {
        $data = $this->getReaderData(CsvReaderInterface::FILE_PEOPLE_CUSTOM_FIELDS);

        return $this->getMultipleCustomFieldsParser()->export($data, self::PERSON_PREFIX, 'person_id');
    }

    /**
     * Returns a collection of people contact data.
     *
     * @return Entity\ContactData[]|Entity\Collection
     */
    private function exportPersonContactData()
    {
        $data = $this->getReaderData(CsvReaderInterface::FILE_PEOPLE_CONTACT_DATA);

        return $this->getMultipleContactDataParser()->export($data, self::PERSON_PREFIX, 'person_id');
    }
}

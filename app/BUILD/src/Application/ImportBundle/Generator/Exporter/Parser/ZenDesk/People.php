<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace Application\ImportBundle\Generator\Exporter\Parser\ZenDesk;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Formatter\FormatterInterface;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Generator\Exporter\Parser\ExportCollectionConfig;
use Application\ImportBundle\Generator\Exporter\Parser\ParserHelperSet;
use Application\ImportBundle\Generator\Exporter\Parser\PeopleStorage;
use Application\ImportBundle\Generator\Exporter\Parser\SkippingException;
use Application\ImportBundle\Reader\ZenDesk\TimeZoneMapper;
use Application\ImportBundle\Reader\ZenDesk\ZenDeskReaderInterface;
use DateTime;
use DateTimeZone;

/**
 * ZenDesk people parser.
 *
 * see https://developer.zendesk.com/rest_api/docs/core/users#time-zone
 *
 * Class People
 */
final class People extends AbstractParser
{
    const ROLE_END_USER = 'end-user';
    const ROLE_AGENT    = 'agent';
    const ROLE_ADMIN    = 'admin';

    /**
     * @var PeopleStorage
     */
    private $people_storage;

    /**
     * Constructor.
     *
     * @param ZenDeskReaderInterface $reader
     * @param FormatterInterface     $formatter
     * @param ParserHelperSet        $helpers
     * @param PeopleStorage          $people_storage
     */
    public function __construct(
        ZenDeskReaderInterface $reader,
        FormatterInterface     $formatter,
        ParserHelperSet        $helpers,
        PeopleStorage          $people_storage
    ) {
        parent::__construct($reader, $formatter, $helpers);
        $this->people_storage = $people_storage;
    }

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
        // We can read data from ZD reader twice because of ZD reader cache support
        return count($this->getPeople());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($this->getPeople())
            ->setPrefix('ZDPerson')
            ->setRefColumn('id')
            ->setMethod('exportPerson')
            ->setAdvanceProgressbar(true)
        ;

        return $this->exportCollection($config);
    }

    /**
     * Returns a person entity.
     *
     * @param array $data
     *
     * @throws \RuntimeException
     *
     * @return Entity\Person
     */
    protected function exportPerson(array $data)
    {
        $entity    = new Entity\Person();
        $formatted = $this->formatter->format($data, [
            'id'          => TransformerInterface::TYPE_STRING,
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, [
                'prefix' => $entity->getDestinationPrefix(),
                'ref'    => 'id',
            ]),
            'name'            => TransformerInterface::TYPE_STRING,
            'email'           => TransformerInterface::TYPE_STRING,
            'time_zone'       => TransformerInterface::TYPE_STRING,
            'role'            => TransformerInterface::TYPE_STRING,
            'created_at'      => TransformerInterface::TYPE_DATE,
            'user_fields'     => TransformerInterface::TYPE_ARRAY,
            'tags'            => TransformerInterface::TYPE_ARRAY,
            'organization_id' => TransformerInterface::TYPE_STRING,
            'is_deleted'      => TransformerInterface::TYPE_BOOLEAN,
        ]);

        if (!$formatted['email']) {
            throw new SkippingException('Person without email, skipping', $formatted);
        }

        try {
            $time_zone = TimeZoneMapper::getTimeZoneName($formatted['time_zone']);
        } catch (\RuntimeException $e) {
            $time_zone = $formatted['time_zone'];
        }

        $entity
            ->setRawData($data)
            ->setDestination($formatted['destination'])
            ->setOid($formatted['id'])
            ->addEmail($formatted['email'])
            ->setName($formatted['name'])
            ->setTimezone(new DateTimeZone($time_zone))
            ->setOrganization($this->getOrganizationName($formatted['organization_id']))
            ->setDateCreated($formatted['created_at'])
        ;

        if ($formatted['is_deleted']) {
            $entity->setAsDisabled(true);

            if (in_array($data['role'], [self::ROLE_ADMIN, self::ROLE_AGENT])) {
                $entity->setAsDeleted(true);
            }
        }

        switch ($data['role']) {
            case self::ROLE_ADMIN:
                $entity->setAsAgent(true)->setAsAdmin(true);
                break;

            case self::ROLE_AGENT:
                $entity->setAsAgent(true);
                break;

            case self::ROLE_END_USER:
                $entity->setAsUser(true);
                break;
        }

        foreach ($formatted['tags'] as $tag) {
            $entity->addLabel($tag);
        }
        foreach ($this->exportCustomFields($formatted) as $custom_field) {
            $entity->addCustomField($custom_field);
        }

        return $entity;
    }

    /**
     * Returns a person custom field entity collection.
     *
     * @param array $person
     *
     * @return Entity\Collection|Entity\CustomField[]
     */
    protected function exportCustomFields(array $person)
    {
        $user_fields = [];
        foreach ($person['user_fields'] as $key => $value) {
            $user_fields[] = [
                'id'    => $key,
                'value' => $value,
            ];
        }

        $config = new ExportCollectionConfig();
        $config
            ->setData($user_fields)
            ->setPrefix('ZDPersonCustomField')
            ->setRefColumn('id')
            ->setMethod('exportCustomField')
        ;

        return $this->exportCollection($config);
    }

    /**
     * Returns a person custom field entity.
     *
     * @param array $data
     *
     * @return Entity\CustomField
     */
    protected function exportCustomField($data)
    {
        $entity    = new Entity\CustomField();
        $formatted = $this->formatter->format($data, [
            'id'          => TransformerInterface::TYPE_STRING,
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, [
                'prefix' => $entity->getDestinationPrefix(),
                'ref'    => 'id',
            ]),
            'value' => TransformerInterface::TYPE_STRING,
        ]);

        $custom_def = $this->getCustomDefById($formatted['id']);
        if (!empty($custom_def['custom_field_options'])) {
            foreach ($custom_def['custom_field_options'] as $option) {
                if ($option['value'] == $formatted['value']) {
                    $formatted['value'] = $option['name'];
                }
            }
        }

        $entity
            ->setRawData($data)
            ->setOid($formatted['id'])
            ->setDestination($formatted['destination'])
            ->setKey($custom_def['title'])
            ->setValue($formatted['value'] ?: '')
        ;

        return $entity;
    }

    /**
     * Returns person custom def by key.
     *
     * @param int $key
     *
     * @return array
     */
    protected function getCustomDefById($key)
    {
        $custom_defs = $this->reader->getPeopleFields();
        foreach ($custom_defs as $custom_def) {
            if ($custom_def['key'] == $key) {
                return $custom_def;
            }
        }

        throw new SkippingException(sprintf('No custom def found with id=%s', $key), $custom_defs);
    }

    /**
     * Returns a collection of people to be exported
     * Gets a collection of people from cache or ZD incremental export request.
     *
     * @return array
     */
    private function getPeople()
    {
        $this->logDebugTimeStart('getPeople', 'Reading people batch');

        $people = $this->people_storage->getPeople();

        if (empty($people)) {
            if ($this->getBatchConfig()->getPeopleEndTime() < new DateTime('-5 minutes')) {
                if ($this->getBatchConfig()->getPeopleEndTime()) {
                    $this->logDebug(sprintf('Reading from time: %s', $this->getBatchConfig()->getPeopleEndTime()->format('Y-m-d H:i:s')));
                } else {
                    $this->logDebug(sprintf('Reading from time: %s', 'Beginning'));
                }

                $people = $this->reader->getPeople($this->getBatchConfig()->getPeopleEndTime());
                if (count($people)) {
                    $this->end_time = $this->reader->getPeopleEndTime($this->getBatchConfig()->getPeopleEndTime());
                    if ($this->end_time == $this->getBatchConfig()->getPeopleEndTime()) {
                        $this->end_time->modify('+1 second');
                    }

                    $this->logDebug(sprintf('New end time: %s', $this->end_time->format('Y-m-d H:i:s')));
                } else {
                    $this->logDebug(sprintf('No more records'));
                }
            } else {
                $this->logAlert('No person was exported due 5 minutes timeout of the last end time');
            }
        }

        $this->logDebug(sprintf('Read %d people', count($people)));
        $this->logDebugTimeEnd('getPeople', 'Done reading people batch');

        return $people;
    }
}

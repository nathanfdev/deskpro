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

namespace Application\ImportBundle\Generator\Exporter\Parser\Json;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Generator\Exporter\Parser\ExportCollectionConfig;
use Application\ImportBundle\Reader\Json\JsonReaderInterface;

/**
 * People json file parser.
 *
 * Class People
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
        return $this->reader->getDirectoryFilesCount(JsonReaderInterface::ENTITY_PERSON_PATH, $this->getBatchNum());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($this->reader->getData(JsonReaderInterface::ENTITY_PERSON_PATH, $this->getBatchNum()))
            ->setPrefix('JSONPerson')
            ->setRefColumn('oid')
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
     * @return Entity\Person|null
     */
    protected function exportPerson(array $data)
    {
        $formatted = $this->formatter->format($data, array(
            'oid'            => TransformerInterface::TYPE_STRING,
            'import_map_key' => TransformerInterface::TYPE_STRING,
            'destination'    => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix'     => 'person_',
                'ref'        => 'oid',
            )),
            'is_agent'              => TransformerInterface::TYPE_BOOLEAN,
            'is_user'               => TransformerInterface::TYPE_BOOLEAN,
            'is_disabled'           => TransformerInterface::TYPE_BOOLEAN,
            'is_deleted'            => TransformerInterface::TYPE_BOOLEAN,
            'is_admin'              => TransformerInterface::TYPE_BOOLEAN,
            'first_name'            => TransformerInterface::TYPE_STRING,
            'last_name'             => TransformerInterface::TYPE_STRING,
            'name'                  => TransformerInterface::TYPE_STRING,
            'override_display_name' => TransformerInterface::TYPE_STRING,
            'password'              => TransformerInterface::TYPE_STRING,
            'password_scheme'       => TransformerInterface::TYPE_STRING,
            'timezone'              => TransformerInterface::TYPE_TIMEZONE,
            'language'              => TransformerInterface::TYPE_STRING,
            'organization'          => TransformerInterface::TYPE_STRING,
            'organization_position' => TransformerInterface::TYPE_STRING,
            'date_created'          => TransformerInterface::TYPE_DATE,
            'emails'                => TransformerInterface::TYPE_ARRAY,
            'labels'                => TransformerInterface::TYPE_ARRAY,
            'user_groups'           => TransformerInterface::TYPE_ARRAY,
            'contact_data'          => TransformerInterface::TYPE_ARRAY,
            'custom_fields'         => TransformerInterface::TYPE_ARRAY,
        ));

            $entity = new Entity\Person();
            $entity
            ->setRawData($data)
            ->setOid($formatted['oid'])
            ->setImportMapKey($formatted['import_map_key'])
            ->setDestination($formatted['destination'])
            ->setAsAgent($formatted['is_agent'])
            ->setAsUser($formatted['is_user'])
            ->setAsAdmin($formatted['is_admin'])
            ->setAsDisabled($formatted['is_disabled'])
            ->setAsDeleted($formatted['is_deleted'])
            ->setFirstName($formatted['first_name'])
            ->setLastName($formatted['last_name'])
            ->setName($formatted['name'])
            ->setOverrideDisplayName($formatted['override_display_name'])
            ->setPassword($formatted['password'])
            ->setPasswordScheme($formatted['password_scheme'])
            ->setLanguage($formatted['language'])
            ->setOrganization($formatted['organization'])
            ->setOrganizationPosition($formatted['organization_position'])
            ->setDateCreated($formatted['date_created'])
            ->setTimezone($formatted['timezone'])
        ;

        foreach ($formatted['emails'] as $email) {
                $entity->addEmail($email);
            }
        foreach ($formatted['labels'] as $label) {
                $entity->addLabel($label);
            }
        foreach ($formatted['user_groups'] as $user_group) {
                $entity->addUserGroup($user_group);
            }

        $contact_data = $this->getContactDataParser()->export($formatted['contact_data']);
        foreach ($contact_data as $contact) {
            $entity->addContact($contact);
        }

        $custom_fields = $this->getCustomFieldsParser()->export($formatted['custom_fields']);
            foreach ($custom_fields as $custom_field) {
                $entity->addCustomField($custom_field);
            }

            return $entity;
        }
}

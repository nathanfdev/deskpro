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
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerException;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;

/**
 * Organizations csv file parser
 *
 * Class Organizations
 * @package Application\ImportBundle\Generator\Exporter\Parser\Csv
 */
class Organizations extends AbstractParser
{
    const ORGANIZATION_PREFIX = 'organization_';

    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_ORGANIZATION;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        return $this->getReaderCount($this->getOrganizationReaderConfig());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $collection    = new Entity\Collection();

        $organizations = $this->getReaderData($this->getOrganizationReaderConfig());
        $contact_data  = $this->exportOrganizationContactData();
        $custom_fields = $this->exportOrganizationCustomFields();

        foreach ($organizations as $num => $organization) {
            $this->advanceProgressBar();

            try {
                $entity = $this->exportOrganization($num, $organization);

                foreach ($contact_data as $contact) {
                    /** @var Entity\ContactData $contact */
                    if ($entity->getDestination() === $contact->getDestination()) {
                        $entity->addContact($contact);
                    }
                }
                foreach ($custom_fields as $custom_field_entity) {
                    /** @var Entity\CustomField $custom_field_entity */
                    if ($entity->getDestination() === $custom_field_entity->getDestination()) {
                        $entity->addCustomField($custom_field_entity);
                    }
                }

                $inline_contact_data = $this->getInlineContactDataParser()->export($organization, $entity->getDestination());
                foreach ($inline_contact_data as $contact) {
                    $entity->addContact($contact);
                }

                $inline_custom_fields = $this->getInlineCustomFieldsParser()->export($entity->getDestination(), $organization);
                foreach ($inline_custom_fields as $custom_field_entity) {
                    $entity->addCustomField($custom_field_entity);
                }

                $collection->attach($entity);
                $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));

            } catch (TransformerException $e) {
                $this->logWarning(sprintf(
                    'Invalid organization record `%d` found (Skipping): %s',
                    $num, $e->getMessage()
                ));

            } catch (\Exception $e) {
                $this->logError(sprintf(
                    'Invalid contact data record `%d` found (Skipping): %s',
                    $num, $e->getMessage()
                ));
            }
        }

        return $collection;
    }

    /**
     * Returns a organization entity
     *
     * @param int   $num
     * @param array $data
     *
     * @return Entity\Organization|null
     */
    private function exportOrganization($num, array $data)
    {
        $configuration = array(
            'id'           => TransformerConfiguration::create(TransformerInterface::TYPE_STRING, array(
                'default' => 'num_' . $num,
            )),
            'destination'  => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix' => self::ORGANIZATION_PREFIX,
                'ref'    => array('original#id', 'name'),
            )),
            'name'         => TransformerInterface::TYPE_STRING,
            'importance'   => TransformerInterface::TYPE_STRING,
            'date_created' => TransformerInterface::TYPE_DATE,
        );

        $formatted = $this->formatter->format($data, $configuration);
        $entity    = new Entity\Organization();
        $entity
            ->setRawData($data)
            ->setDestination($formatted['destination'])
            ->setOid($formatted['id'])
            ->setName($formatted['name'])
            ->setImportance($formatted['importance'])
            ->setPicture($this->getBlobParser()->export(1, self::ORGANIZATION_PREFIX, $data, 'name'))
            ->setDateCreated($formatted['date_created'])
        ;

        return $entity;
    }

    /**
     * Returns a collection of organization custom field data
     *
     * @return Entity\Collection
     */
    private function exportOrganizationCustomFields()
    {
        $config = $this->getReaderConfig(self::FILE_ORGANIZATION_CUSTOM_FIELDS);
        $data   = $this->getReaderData($config);

        return $this->getMultipleCustomFieldsParser()->export($data, self::ORGANIZATION_PREFIX, 'organization_id');
    }

    /**
     * Returns a collection of organization contact data
     *
     * @return Entity\Collection
     */
    private function exportOrganizationContactData()
    {
        $config = $this->getReaderConfig(self::FILE_ORGANIZATION_CONTACT_DATA);
        $data   = $this->getReaderData($config);

        return $this->getMultipleContactDataParser()->export($data, self::ORGANIZATION_PREFIX, 'organization_id');
    }

    /**
     * Returns reader config for organization records
     *
     * @return \Application\ImportBundle\Reader\Csv\CsvConfig
     */
    private function getOrganizationReaderConfig()
    {
        return $this->getReaderConfig(self::FILE_ORGANIZATIONS);
    }
}

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

namespace Application\ImportBundle\Generator\Exporter\Parser\Csv\ContactData;

use Application\ImportBundle\ContactData\ContactDataFactory;
use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\AbstractGenerator;
use Application\ImportBundle\Generator\Exporter\Helper\ColumnHelper;
use Application\ImportBundle\Generator\Exporter\Formatter\DestinationFormatter;
use Application\ImportBundle\Generator\Exporter\Parser\NoColumnException;

/**
 * Class MultipleContactData
 * @package Application\ImportBundle\Generator\Exporter\Parser\Csv\ContactData
 */
class MultipleContactData extends AbstractGenerator
{
    /**
     * Returns a collection of organization contact data entities
     *
     * @param array  $data
     * @param string $destination_prefix
     * @param string $ref_column
     *
     * @return Entity\Collection
     */
    public function export(array $data, $destination_prefix, $ref_column)
    {
        $collection   = new Entity\Collection();
        $contact_info = $this->exportContactDataFields($data, $destination_prefix, $ref_column);

        foreach ($contact_info as $destination => $contacts) {
            foreach ($contacts as $oid => $contact) {
                try {
                    if ($this->isContactValid($contact)) {
                        $handler = ContactDataFactory::getHandler($contact['contact_type']);
                        $entity  = $handler->toEntity($contact);
                        $entity
                            ->setOid($oid)
                            ->setDestination($destination)
                        ;

                        $collection->attach($entity);
                        $this->logInfo(sprintf('Entity `%s%s` parsed successfully!', $entity->getDestination()));
                    } else {
                        $this->logWarning(sprintf('Invalid contact record `%d` found (Skipping)', $destination));
                    }

                } catch (NoColumnException $e) {
                    $this->logWarning(sprintf(
                        'Invalid contact record `%d` found (Skipping): %s',
                        $destination, $e->getMessage()
                    ));
                }

            }
        }

        return $collection;
    }

    /**
     * Merges contact data fields to array
     *
     * @param array  $data
     * @param string $destination_prefix
     * @param string $ref_column
     *
     * @return array
     */
    protected function exportContactDataFields(array $data, $destination_prefix, $ref_column)
    {
        $contact_info = array();
        foreach ($data as $num => $field) {
            try {
                if ($this->isContactFieldValid($field, $ref_column)) {
                    $destination = DestinationFormatter::transform($destination_prefix, $field[$ref_column]);
                    $contact_info[$destination][$field['contact_id']][$field['field_name']] = $field['value'];

                    $this->logInfo(sprintf('Contact field `%s` parsed successfully!', $destination));
                } else {
                    $this->logWarning(sprintf('Invalid contact field record `%d` found (Skipping)', $num));
                }

            } catch (NoColumnException $e) {
                $this->logWarning(sprintf(
                    'Invalid contact field record `%d` found (Skipping): %s',
                    $num, $e->getMessage()
                ));
            }
        }

        return $contact_info;
    }

    /**
     * Check if contact data field has all required columns
     *
     * @param array  $contact
     * @param string $ref_column
     *
     * @return bool
     */
    protected function isContactFieldValid(array $contact, $ref_column)
    {
        $columns = array(
            $ref_column,
            'contact_id',
            'field_name',
            'value',
        );

        return ColumnHelper::hasRequiredColumns($contact, $columns);
    }

    /**
     * Check if contact data has all required columns
     *
     * @param array $contact
     * @return bool
     */
    protected function isContactValid(array $contact)
    {
        $columns = array(
            'contact_type',
        );

        return ColumnHelper::hasRequiredColumns($contact, $columns);
    }
}

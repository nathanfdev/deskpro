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

use Application\ImportBundle\Reader\Json\JsonConfig;
use Application\ImportBundle\Reader\Json\JsonReaderInterface;
use Application\ImportBundle\Entity;

/**
 * Abstract json parser
 *
 * Class AbstractParser
 * @package Application\ImportBundle\Generator\Exporter\Parser\Json
 */
abstract class AbstractParser extends \Application\ImportBundle\Generator\Exporter\Parser\AbstractParser
{
    /**
     * @var JsonReaderInterface
     */
    protected $reader;

    /**
     * Constructor
     *
     * @param JsonReaderInterface $reader
     */
    public function __construct(JsonReaderInterface $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Get json reader config
     * todo add support to exclude done files
     *
     * @param string $record_type
     * @return JsonConfig
     */
    protected function getReaderConfig($record_type)
    {
        return new JsonConfig(sprintf('%s/%s', $this->config->getInputPath(), $record_type));
    }

    /**
     * Exports custom fields
     *
     * @param array $custom_fields
     * @return Entity\Collection
     */
    protected function exportCustomFields(array $custom_fields)
    {
        $collection = new Entity\Collection();
        foreach ($custom_fields as $num => $custom_field) {
            if ($this->hasRequiredCustomFieldColumns($custom_field) === false) {
                $this->logWarning(sprintf('Invalid custom field record found (Skipping): %d', $num));
            } else {
                $entity = new Entity\CustomField();
                $entity
                    ->setOid($custom_field['oid'])
                    ->setKey($custom_field['key'])
                    ->setValue($custom_field['value']);

                $collection->attach($entity);
            }
        }

        return $collection;
    }

    /**
     * Check if ticket message attachment has all required columns
     *
     * @param array $attachment
     * @return bool
     */
    protected function hasRequiredAttachmentColumns(array $attachment)
    {
        return $this->hasRequiredColumns($attachment, array(
            'oid',
            'person',
            'blob_data',
            'blob_url',
            'blob_path',
            'file_name',
            'content_type',
            'is_inline',
        ));
    }

    /**
     * Check if person has all required columns
     *
     * @param array $custom_field
     * @return bool
     */
    protected function hasRequiredCustomFieldColumns(array $custom_field)
    {
        return $this->hasRequiredColumns($custom_field, array(
            'oid',
            'key',
            'value',
        ));
    }
}

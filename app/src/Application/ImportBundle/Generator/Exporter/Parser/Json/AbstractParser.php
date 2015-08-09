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

use Application\ImportBundle\ContactData\ContactDataFactory;
use Application\ImportBundle\Generator\Exporter\Formatter\FormatterInterface;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerException;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Reader\Json\JsonConfig;
use Application\ImportBundle\Reader\Json\JsonReaderInterface;
use Application\ImportBundle\Entity;
use Exception;

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
     * @var FormatterInterface
     */
    protected $formatter;

    /**
     * Constructor
     *
     * @param JsonReaderInterface $reader
     * @param FormatterInterface  $formatter
     */
    public function __construct(JsonReaderInterface $reader, FormatterInterface $formatter)
    {
        $this->reader    = $reader;
        $this->formatter = $formatter;
    }

    /**
     * Get json reader config
     *
     * @param string $record_type
     * @return JsonConfig
     */
    protected function getReaderConfig($record_type)
    {
        return new JsonConfig(sprintf(
            '%s/%d/%s',
            $this->config->getInputPath(), $this->getBatchConfig()->getId() + 1, $record_type
        ));
    }

    /**
     * Returns a collection of contact data entities
     *
     * @param array $contact_data
     * @return Entity\Collection
     */
    protected function exportContactData(array $contact_data)
    {
        $collection = new Entity\Collection();
        foreach ($contact_data as $num => $contact) {
            try {
                $entity = $this->exportContact($contact);
                if ($entity) {
                    $collection->attach($entity);
                    $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));
                } else {
                    $this->logWarning(sprintf('Invalid contact data record found (Skipping): %d', $num));
                }

                $collection->attach($entity);
                $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));

            } catch (TransformerException $e) {
                $this->logError(sprintf(
                    'Invalid contact data record `%d` found (Skipping): %s',
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
     * Returns a contact data entity
     *
     * @param array $data
     * @return Entity\ContactData|null
     *
     */
    protected function exportContact(array $data)
    {
        $configuration = array(
            'oid'          => TransformerInterface::TYPE_STRING,
            'destination'  => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix' => 'contact_data_',
                'ref'    => 'oid',
            )),
            'contact_type' => TransformerInterface::TYPE_STRING,
            'comment'      => TransformerInterface::TYPE_STRING,
        );

        $formatted = $this->formatter->format($data, $configuration);
        $handler   = ContactDataFactory::getHandler($formatted['contact_type']);
        $entity    = $handler->toEntity($data);
        $entity
            ->setOid($formatted['oid'])
            ->setDestination($formatted['destination'])
        ;

        return $entity;
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
            try {
                $entity = $this->exportCustomField($custom_field);
                $collection->attach($entity);

            } catch (TransformerException $e) {
                $this->logWarning(sprintf(
                    'Invalid custom field record `%d` found (Skipping): %s',
                    $num, $e->getMessage()
                ));
            }
        }

        return $collection;
    }

    /**
     * Returns a custom field entity
     *
     * @param array $data
     * @return Entity\CustomField|null
     */
    protected function exportCustomField(array $data)
    {
        $configuration = array(
            'oid'         => TransformerInterface::TYPE_STRING,
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix' => 'custom_field_',
                'ref'    => 'oid',
            )),
            'key'         => TransformerInterface::TYPE_STRING,
            'value'       => TransformerInterface::TYPE_STRING,
        );

        $formatted = $this->formatter->format($data, $configuration);
        $entity    = new Entity\CustomField();
        $entity
            ->setRawData($data)
            ->setOid($formatted['oid'])
            ->setDestination($formatted['destination'])
            ->setKey($formatted['key'])
            ->setValue($formatted['value'])
        ;

        return $entity;
    }

    /**
     * Returns an attachment entity
     *
     * @param array $data
     * @return Entity\Attachment|null
     */
    protected function exportAttachment(array $data = null)
    {
        $configuration = array(
            'person'    => TransformerInterface::TYPE_STRING,
            'is_inline' => TransformerInterface::TYPE_BOOLEAN,
        );

        $formatted = $this->formatter->format($data, $configuration);

        /** @var Entity\Attachment $entity */
        $entity = $this->exportBlob($data, new Entity\Attachment());
        if ($entity) {
            $entity
                ->setPersonEmail($formatted['person'])
                ->setAsInline($formatted['is_inline']);

            return $entity;
        }

        return null;
    }

    /**
     * Returns a blob entity
     *
     * @param array|null       $data
     * @param Entity\Blob|null $entity
     *
     * @return Entity\Blob|null
     */
    protected function exportBlob(array $data = null, Entity\Blob $entity = null)
    {
        if (empty($data)) {
            return null;
        }

        $configuration = array(
            'oid'          => TransformerInterface::TYPE_STRING,
            'destination'  => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix' => 'attachment_',
                'ref'    => 'oid',
            )),
            'blob_data'    => TransformerInterface::TYPE_STRING,
            'blob_url'     => TransformerInterface::TYPE_STRING,
            'blob_path'    => TransformerInterface::TYPE_STRING,
            'file_name'    => TransformerInterface::TYPE_STRING,
            'content_type' => TransformerInterface::TYPE_STRING,
        );

        $formatted = $this->formatter->format($data, $configuration);
        $entity    = $entity ? : new Entity\Blob();
        $entity
            ->setRawData($data)
            ->setOid($formatted['oid'])
            ->setDestination($formatted['destination'])
            ->setBlobData($formatted['blob_data'])
            ->setBlobUrl($formatted['blob_url'])
            ->setBlobPath($formatted['blob_path'])
            ->setFileName($formatted['file_name'])
            ->setContentType($formatted['content_type'])
        ;

        return $entity;
    }

    /**
     * Returns batch config
     *
     * @return BatchConfig
     * @throws Exception
     */
    protected function getBatchConfig()
    {
        if ($this->config->getExporterBatchConfig()) {
            return $this->config->getExporterBatchConfig();
        }

        throw new Exception('Batch config is not defined');
    }
}

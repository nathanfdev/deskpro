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

namespace Application\ImportBundle\Generator\Exporter\Parser\Csv\Helper\ContactData;

use Application\ImportBundle\ContactData\ContactDataFactory;
use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\AbstractGenerator;
use Application\ImportBundle\Generator\Exporter\Formatter\FormatterInterface;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerException;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;

/**
 * Class MultipleContactData
 * @package Application\ImportBundle\Generator\Exporter\Parser\Csv\Helper\ContactData
 */
class MultipleContactData extends AbstractGenerator
{
    /**
     * @var FormatterInterface
     */
    private $formatter;

    /**
     * Constructor
     *
     * @param FormatterInterface $formatter
     */
    public function __construct(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }

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
                    if ( ! isset($contact['contact_type'])) {
                        continue;
                    }

                    $handler = ContactDataFactory::getHandler($contact['contact_type']);
                    $entity  = $handler->toEntity($contact);
                    $entity
                        ->setOid($oid)
                        ->setDestination($destination)
                    ;

                    $collection->attach($entity);
                    $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));

                } catch (\RuntimeException $e) {
                    $this->logWarning(sprintf(
                        'Invalid contact field record `%d` found (Skipping): %s',
                        $oid, $e->getMessage()
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
                if (isset($field[$ref_column])) {
                    $field['destination'] = $destination_prefix . $field[$ref_column];
                }

                $configuration = array(
                    $ref_column   => TransformerInterface::TYPE_STRING,
                    'contact_id'  => TransformerInterface::TYPE_STRING,
                    'destination' => TransformerInterface::TYPE_DESTINATION,
                    'field_name'  => TransformerInterface::TYPE_STRING,
                    'value'       => TransformerInterface::TYPE_STRING,
                );

                $formatted = $this->formatter->format($field, $configuration);

                if ( ! $formatted['destination']) {
                    $this->logWarning(sprintf('Invalid contact field record `%d` found (Skipping): Empty destination', $num));
                }
                if ( ! $formatted['contact_id']) {
                    $this->logWarning(sprintf('Invalid contact field record `%d` found (Skipping): Empty contact_id', $num));
                }
                if ( ! $formatted['field_name']) {
                    $this->logWarning(sprintf('Invalid contact field record `%d` found (Skipping): Empty field_name', $num));
                }

                $contact_info[$formatted['destination']][$formatted['contact_id']][$formatted['field_name']] = $formatted['value'];
                $this->logInfo(sprintf('Contact field `%s` parsed successfully!', $formatted['destination']));

            } catch (TransformerException $e) {
                $this->logWarning(sprintf(
                    'Invalid contact field record `%d` found (Skipping): %s',
                    $num, $e->getMessage()
                ));
            }
        }

        return $contact_info;
    }
}

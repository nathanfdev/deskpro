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

namespace Application\ImportBundle\Generator\Exporter\Parser\Json\Helper;

use Application\ImportBundle\ContactData\ContactDataFactory;
use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Generator\Exporter\Parser\AbstractParserFormatterHelper;
use Application\ImportBundle\Generator\Exporter\Parser\ExportCollectionConfig;

/**
 * Class ContactData.
 */
class ContactData extends AbstractParserFormatterHelper
{
    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_CONTACT_DATA;
    }

    /**
     * Returns a collection of contact data entities.
     *
     * @param array $contact_data
     *
     * @return Entity\Collection
     */
    public function export(array $contact_data)
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($contact_data)
            ->setPrefix('JSONContactData')
            ->setRefColumn('oid')
            ->setMethod('exportContact')
        ;

        return $this->exportCollection($config);
    }

    /**
     * Returns a contact data entity.
     *
     * @param array $data
     *
     * @return Entity\ContactData|null
     */
    protected function exportContact(array $data)
    {
        $formatted = $this->formatter->format($data, [
            'oid'         => TransformerInterface::TYPE_STRING,
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, [
                'prefix' => 'contact_data_',
                'ref'    => 'oid',
            ]),
            'contact_type' => TransformerInterface::TYPE_STRING,
            'comment'      => TransformerInterface::TYPE_STRING,
        ]);

        $handler = ContactDataFactory::getHandler($formatted['contact_type']);
        $entity  = $handler->toEntity($data);
        $entity
            ->setOid($formatted['oid'])
            ->setDestination($formatted['destination'])
        ;

        return $entity;
    }
}

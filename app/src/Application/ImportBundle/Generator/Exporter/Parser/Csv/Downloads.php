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
 * Downloads csv file parser.
 *
 * Class Downloads
 */
final class Downloads extends AbstractParser
{
    const DOWNLOAD_PREFIX = 'download_';

    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_DOWNLOAD;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        return $this->getReaderCount(CsvReaderInterface::FILE_DOWNLOADS);
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($this->getReaderData(CsvReaderInterface::FILE_DOWNLOADS))
            ->setPrefix('CSVDownload')
            ->setRefColumn('id')
            ->setMethod('exportDownload')
            ->setAdvanceProgressbar(true)
        ;

        return $this->exportCollection($config);
    }

    /**
     * Returns a download entity
     * Download data contains attachment params.
     *
     * @param array $data
     * @param int   $num
     *
     * @return Entity\Download
     */
    protected function exportDownload(array $data, $num)
    {
        $formatted = $this->formatter->format($data, array(
            'id'          => TransformerConfiguration::create(TransformerInterface::TYPE_STRING, array(
                'default' => 'num_'.$num,
            )),
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix'  => self::DOWNLOAD_PREFIX,
                'ref'     => 'id',
            )),
            'person'       => TransformerInterface::TYPE_STRING,
            'title'        => TransformerInterface::TYPE_STRING,
            'content'      => TransformerInterface::TYPE_STRING,
            'slug'         => TransformerInterface::TYPE_STRING,
            'language'     => TransformerInterface::TYPE_STRING,
            'category'     => TransformerInterface::TYPE_STRING,
            'status'       => TransformerInterface::TYPE_STRING,
            'date_created' => TransformerInterface::TYPE_DATE,
            'label'        => TransformerInterface::TYPE_STRING,
        ));

            $entity = new Entity\Download();
            $entity
            ->setRawData($data)
            ->setDestination($formatted['destination'])
            ->setOid($formatted['id'])
            ->setPersonEmail($formatted['person'])
            ->setTitle($formatted['title'])
            ->setContent($formatted['content'])
            ->setSlug($formatted['slug'])
            ->setLanguage($formatted['language'])
            ->setCategory($formatted['category'])
            ->setStatus($formatted['status'])
            ->setDateCreated($formatted['date_created'])
            ->setAttachment($this->getAttachmentParser()->exportAttachment($num, self::DOWNLOAD_PREFIX, $data, 'person'))
        ;

        if ($formatted['label']) {
            $entity->addLabel($formatted['label']);
            }

            return $entity;
        }
}

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

use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerException;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Generator\Writer\Json\Destination;
use Application\ImportBundle\Entity;

/**
 * Downloads json file parser
 *
 * Class Downloads
 * @package Application\ImportBundle\Generator\Exporter\Parser\Json
 */
final class Downloads extends AbstractParser
{
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
        return $this->reader->getDirectoryFilesCount($this->getDownloadReaderConfig());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $collection = new Entity\Collection();
        $downloads  = $this->reader->getData($this->getDownloadReaderConfig());

        foreach ($downloads as $num => $download) {
            $this->advanceProgressBar();

            try {
                $entity = $this->exportDownload($download);

                $collection->attach($entity);
                $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));

            } catch (TransformerException $e) {
                $this->logWarning(sprintf(
                    'Invalid download record `%d` found (Skipping): %s',
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
     * Returns a download entity
     *
     * @param array $data
     * @return Entity\Download|null
     */
    private function exportDownload(array $data)
    {
        $configuration = array(
            'oid'            => TransformerInterface::TYPE_STRING,
            'destination'    => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix' => 'download_',
                'ref'    => 'oid',
            )),
            'person'         => TransformerInterface::TYPE_STRING,
            'title'          => TransformerInterface::TYPE_STRING,
            'content'        => TransformerInterface::TYPE_STRING,
            'slug'           => TransformerInterface::TYPE_STRING,
            'language'       => TransformerInterface::TYPE_STRING,
            'total_rating'   => TransformerInterface::TYPE_STRING,
            'num_comments'   => TransformerInterface::TYPE_INT,
            'num_ratings'    => TransformerInterface::TYPE_INT,
            'num_downloads'  => TransformerInterface::TYPE_INT,
            'view_count'     => TransformerInterface::TYPE_INT,
            'category'       => TransformerInterface::TYPE_STRING,
            'status'         => TransformerInterface::TYPE_STRING,
            'date_created'   => TransformerInterface::TYPE_DATE,
            'date_published' => TransformerInterface::TYPE_DATE,
            'attachment'     => TransformerInterface::TYPE_ARRAY,
            'labels'         => TransformerInterface::TYPE_ARRAY,
        );

        $formatted = $this->formatter->format($data, $configuration);
        $entity    = new Entity\Download();
        $entity
            ->setOid($formatted['oid'])
            ->setDestination($formatted['destination'])
            ->setPersonEmail($formatted['person'])
            ->setTitle($formatted['title'])
            ->setContent($formatted['content'])
            ->setSlug($formatted['slug'])
            ->setLanguage($formatted['language'])
            ->setTotalRating($formatted['total_rating'])
            ->setNumComments($formatted['num_comments'])
            ->setNumRatings($formatted['num_ratings'])
            ->setNumDownloads($formatted['num_downloads'])
            ->setViewCount($formatted['view_count'])
            ->setCategory($formatted['category'])
            ->setStatus($formatted['status'])
            ->setDateCreated($formatted['date_created'])
            ->setDatePublished($formatted['date_published'])
            ->setAttachment($this->exportAttachment($formatted['attachment']))
        ;

        foreach ($formatted['labels'] as $label) {
            $entity->addLabel($label);
        }

        return $entity;
    }

    /**
     * Returns record type reader config
     *
     * @return \Application\ImportBundle\Reader\Json\JsonConfig
     */
    private function getDownloadReaderConfig()
    {
        return $this->getReaderConfig(Destination\DestinationInterface::ENTITY_DOWNLOAD_PATH);
    }
}

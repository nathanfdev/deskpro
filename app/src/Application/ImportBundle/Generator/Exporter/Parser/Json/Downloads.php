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

use Application\ImportBundle\Generator\Writer\Json\Destination;
use Application\ImportBundle\Entity;
use DateTime;

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
        return $this->reader->getDirectoryFilesCount($this->getConfig());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $collection = new Entity\Collection();
        $downloads  = $this->reader->getData($this->getConfig());

        foreach ($downloads as $num => $download) {
            $this->advanceProgressBar();

            if ($this->hasRequiredDownloadColumns($download) === false) {
                $this->logWarning(sprintf('Invalid download record found (Skipping): %d', $num));
            } else {
                $entity = new Entity\Download();
                $entity
                    ->setDestination('download_' . $download['oid'])
                    ->setOid($download['oid'])
                    ->setPersonEmail($download['person'])
                    ->setTitle($download['title'])
                    ->setContent($download['content'])
                    ->setLanguage($download['language'])
                    ->setSlug($download['slug'])
                    ->setTotalRating($download['total_rating'])
                    ->setNumComments($download['num_comments'])
                    ->setNumRatings($download['num_ratings'])
                    ->setNumDownloads($download['num_downloads'])
                    ->setViewCount($download['view_count'])
                    ->setCategory($download['category'])
                    ->setStatus($download['status'])
                    ->setDateCreated(new DateTime($download['date_created']));

                if ($download['date_published']) {
                    $entity->setDatePublished(new DateTime($download['date_published']));
                }
                if ($download['attachment']) {
                    $entity->setAttachment($this->exportAttachment($download['attachment']));
                }

                foreach ($download['labels'] as $label) {
                    $entity->addLabel($label);
                }

                $collection->attach($entity);
                $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));
            }
        }

        return $collection;
    }

    /**
     * Returns download attachment entity on success or null on failure
     *
     * @param array $attachment
     * @return Entity\Attachment|null
     */
    private function exportAttachment(array $attachment)
    {
        if ($this->hasRequiredAttachmentColumns($attachment) === false) {
            $this->logWarning('Invalid download attachment record found (Skipping)');
        } else {
            $entity = new Entity\Attachment();
            $entity
                ->setOid($attachment['oid'])
                ->setPersonEmail($attachment['person'])
                ->setBlobData($attachment['blob_data'])
                ->setBlobData($attachment['blob_url'])
                ->setBlobData($attachment['blob_path'])
                ->setFileName($attachment['file_name'])
                ->setContentType($attachment['content_type'])
                ->setAsInline($attachment['is_inline']);

            return $entity;
        }

        return null;
    }

    /**
     * Returns record type reader config
     *
     * @return \Application\ImportBundle\Reader\Json\JsonConfig
     */
    private function getConfig()
    {
        return $this->getReaderConfig(Destination\DestinationInterface::ENTITY_DOWNLOAD_PATH);
    }

    /**
     * Check if download has all required columns
     *
     * @param array $download
     * @return bool
     */
    private function hasRequiredDownloadColumns(array $download)
    {
        $columns = array(
            'oid',
            'person',
            'title',
            'content',
            'language',
            'slug',
            'total_rating',
            'num_comments',
            'num_ratings',
            'num_downloads',
            'view_count',
            'category',
            'status',
            'attachment',
            'date_created',
            'date_published',
            'labels',
        );

        return $this->hasRequiredColumns($download, $columns) && is_array($download['labels']);
    }
}

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
use Application\ImportBundle\Generator\Exporter\Parser\NoColumnException;

/**
 * Downloads csv file parser
 *
 * Class Downloads
 * @package Application\ImportBundle\Generator\Exporter\Parser\Csv
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
        return $this->getReaderCount($this->getConfig());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $collection  = new Entity\Collection();
        $downloads   = $this->getReaderData($this->getConfig());
        $attachments = $this->exportDownloadAttachments();

        foreach ($downloads as $num => $download) {
            $this->advanceProgressBar();

            try {
                $this->validateDownload($download);

                $entity = new Entity\Download();
                $entity
                    ->setDestination(self::DOWNLOAD_PREFIX . $download['id'])
                    ->setOid($download['id'])
                    ->setPersonEmail($download['person'])
                    ->setTitle($download['title'])
                    ->setContent($download['content'])
                    ->setSlug($download['slug'])
                    ->setLanguage($download['language'])
                    ->setCategory($download['category'])
                    ->setStatus($download['status'])
                    ->setDateCreated($this->getFromStringOrCurrentDateTime($download['date_created']));

                foreach ($attachments as $attachment) {
                    /** @var Entity\Attachment $attachment */
                    if ($attachment->getDestination() === $entity->getDestination()) {
                        $entity->setAttachment($attachment);
                    }
                }

                $collection->attach($entity);
                $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));

            } catch (NoColumnException $e) {
                $this->logError(sprintf(
                    'Invalid download record `%d` found (Skipping): %s',
                    $num, $e->getMessage()
                ));
            }
        }

        return $collection;
    }

    /**
     * Returns a collection of download attachments
     *
     * @return Entity\Collection
     */
    private function exportDownloadAttachments()
    {
        return $this->exportAttachments($this->getDownloadAttachmentsConfig(), self::DOWNLOAD_PREFIX, 'download_id');
    }

    /**
     * Check if download has all required columns
     *
     * @param array $download
     * @return bool
     */
    private function validateDownload(array $download)
    {
        $columns = array(
            'id',
            'person',
            'title',
            'content',
            'slug',
            'language',
            'category',
            'status',
            'date_created',
            'label',
        );

        return $this->hasRequiredColumns($download, $columns);
    }

    /**
     * Returns record type reader config
     *
     * @return \Application\ImportBundle\Reader\Csv\CsvConfig
     */
    private function getConfig()
    {
        return $this->getReaderConfig(self::FILE_DOWNLOADS);
    }

    /**
     * Returns reader of download attachments records config
     *
     * @return \Application\ImportBundle\Reader\Csv\CsvConfig
     */
    private function getDownloadAttachmentsConfig()
    {
        return $this->getReaderConfig(self::FILE_DOWNLOAD_ATTACHMENTS);
    }
}

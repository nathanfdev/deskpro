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

/**
 * Downloads csv file parser
 *
 * Class Downloads
 * @package Application\ImportBundle\Generator\Exporter\Parser\Csv
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
        return $this->reader->getRowsCount($this->getConfig());
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
                    ->setDestination('download_' . $num)
                    ->setOid($num)
                    ->setPersonEmail($download['person'])
                    ->setTitle($download['title'])
                    ->setContent($download['content'])
                    ->setSlug($download['slug'])
                    ->setLanguage($download['language'])
                    ->setCategory($download['category'])
                    ->setStatus($download['status'])
                    ->setDateCreated($this->getFromStringOrCurrentDateTime($download['date_created']));

                $collection->attach($entity);
                $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));
            }
        }

        return $collection;
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
     * Check if download has all required columns
     *
     * @param array $download
     * @return bool
     */
    private function hasRequiredDownloadColumns(array $download)
    {
        return $this->hasRequiredColumns($download, array(
            'person',
            'title',
            'content',
            'slug',
            'language',
            'category',
            'status',
            'date_created',
            'label',
        ));
    }
}

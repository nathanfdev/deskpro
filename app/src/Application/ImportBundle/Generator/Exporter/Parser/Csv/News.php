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
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerException;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;

/**
 * News csv file parser
 *
 * Class News
 * @package Application\ImportBundle\Generator\Exporter\Parser\Csv
 */
final class News extends AbstractParser
{
    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_NEWS;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        return $this->getReaderCount($this->getNewsReaderConfig());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $collection = new Entity\Collection();
        $news_list  = $this->getReaderData($this->getNewsReaderConfig());

        foreach ($news_list as $num => $data) {
            $this->advanceProgressBar();

            try {
                $entity = $this->exportNews($num, $data);

                $collection->attach($entity);
                $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));

            } catch (TransformerException $e) {
                $this->logTransformerException('CSVNews', $this->getEntityType(), 'title', $e);
            } catch (\Exception $e) {
                $this->logUnknownException('CSVNews', $this->getEntityType(), 'title', $e, $data);
            }
        }

        return $collection;
    }

    /**
     * Returns a news entity
     *
     * @param int   $num
     * @param array $data
     *
     * @return Entity\News|null
     */
    private function exportNews($num, array $data)
    {
        $formatted = $this->formatter->format($data, array(
            'id'             => TransformerConfiguration::create(TransformerInterface::TYPE_STRING, array(
                'default' => 'num_' . $num,
            )),
            'destination'    => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix' => 'news_',
                'ref'    => 'id',
            )),
            'person'         => TransformerInterface::TYPE_STRING,
            'title'          => TransformerInterface::TYPE_STRING,
            'content'        => TransformerInterface::TYPE_STRING,
            'slug'           => TransformerInterface::TYPE_STRING,
            'language'       => TransformerInterface::TYPE_STRING,
            'status'         => TransformerInterface::TYPE_STRING,
            'date_created'   => TransformerInterface::TYPE_DATE,
            'date_published' => TransformerInterface::TYPE_DATE,
            'category'       => TransformerInterface::TYPE_STRING,
            'label'          => TransformerInterface::TYPE_STRING,
        ));

        $entity = new Entity\News();
        $entity
            ->setRawData($data)
            ->setDestination($formatted['destination'])
            ->setOid($formatted['id'])
            ->setPersonEmail($formatted['person'])
            ->setLanguage($formatted['language'])
            ->setSlug($formatted['slug'])
            ->setTitle($formatted['title'])
            ->setContent($formatted['content'])
            ->setSlug($formatted['slug'])
            ->setStatus($formatted['status'])
            ->setDateCreated($formatted['date_created'])
            ->setCategory($formatted['category'])
            ->setDatePublished($formatted['date_published'])
        ;

        if ($formatted['label']) {
            $entity->addLabel($formatted['label']);
        }

        return $entity;
    }

    /**
     * Returns record type reader config
     *
     * @return \Application\ImportBundle\Reader\Csv\CsvConfig
     */
    private function getNewsReaderConfig()
    {
        return $this->getReaderConfig(self::FILE_NEWS);
    }
}

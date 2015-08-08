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
 * Articles csv file parser
 *
 * Class Articles
 * @package Application\ImportBundle\Generator\Exporter\Parser\Csv
 */
final class Articles extends AbstractParser
{
    const ARTICLE_PREFIX = 'article_';

    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_ARTICLE;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        return $this->getReaderCount($this->getArticleReaderConfig());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $collection    = new Entity\Collection();

        $articles      = $this->getReaderData($this->getArticleReaderConfig());
        $custom_fields = $this->exportArticleCustomFields();

        foreach ($articles as $num => $article) {
            $this->advanceProgressBar();

            try {
                $entity = $this->exportArticle($num, $article);

                foreach ($custom_fields as $custom_field_entity) {
                    /** @var Entity\CustomField $custom_field_entity */
                    if ($entity->getDestination() === $custom_field_entity->getDestination()) {
                        $entity->addCustomField($custom_field_entity);
                    }
                }

                $inline_custom_fields = $this->exportInlineCustomFields($entity->getDestination(), $article);
                foreach ($inline_custom_fields as $custom_field_entity) {
                    $entity->addCustomField($custom_field_entity);
                }

                $collection->attach($entity);
                $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));

            } catch (TransformerException $e) {
                $this->logWarning(sprintf(
                    'Invalid article record `%d` found (Skipping): %s',
                    $num, $e->getMessage()
                ));
            }
        }

        return $collection;
    }

    /**
     * Returns an article entity
     *
     * @param int   $num
     * @param array $data
     *
     * @return Entity\Article|null
     */
    private function exportArticle($num, array $data)
    {
        $configuration = array(
            'id' => TransformerConfiguration::create(TransformerInterface::TYPE_STRING, array(
                'default' => 'num_' . $num,
            )),
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix'  => self::ARTICLE_PREFIX,
                'ref'     => 'id',
            )),
            'person'       => TransformerInterface::TYPE_STRING,
            'title'        => TransformerInterface::TYPE_STRING,
            'content'      => TransformerInterface::TYPE_STRING,
            'slug'         => TransformerInterface::TYPE_STRING,
            'language'     => TransformerInterface::TYPE_STRING,
            'status'       => TransformerInterface::TYPE_STRING,
            'category'     => TransformerInterface::TYPE_STRING,
            'label'        => TransformerInterface::TYPE_STRING,
            'date_created' => TransformerInterface::TYPE_DATE,
        );

        $formatted = $this->formatter->format($data, $configuration);
        $entity    = new Entity\Article();
        $entity
            ->setRawData($data)
            ->setDestination($formatted['destination'])
            ->setOid($formatted['id'])
            ->setPersonEmail($formatted['person'])
            ->setTitle($formatted['title'])
            ->setContent($formatted['content'])
            ->setSlug($formatted['slug'])
            ->setLanguage($formatted['language'])
            ->setDateCreated($formatted['date_created'])
            ->setStatus($formatted['status'])
        ;

        if ($formatted['label']) {
            $entity->addLabel($formatted['label']);
        }
        if ($formatted['category']) {
            $entity->addCategory($formatted['category']);
        }

        return $entity;
    }

    /**
     * Returns a collection of articles custom field data
     *
     * @return Entity\Collection
     */
    private function exportArticleCustomFields()
    {
        $config = $this->getReaderConfig(self::FILE_ARTICLE_CUSTOM_FIELDS);
        return $this->exportCustomFields($config, self::ARTICLE_PREFIX, 'article_id');
    }

    /**
     * Returns record type reader config
     *
     * @return \Application\ImportBundle\Reader\Csv\CsvConfig
     */
    private function getArticleReaderConfig()
    {
        return $this->getReaderConfig(self::FILE_ARTICLES);
    }
}

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
 * Articles json file parser
 *
 * Class Articles
 * @package Application\ImportBundle\Generator\Exporter\Parser\Json
 */
final class Articles extends AbstractParser
{
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
        return $this->reader->getDirectoryFilesCount($this->getArticleReaderConfig());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $collection = new Entity\Collection();
        $articles = $this->reader->getData($this->getArticleReaderConfig());

        foreach ($articles as $num => $article) {
            $this->advanceProgressBar();

            try {
                $entity = $this->exportArticle($article);

                $collection->attach($entity);
                $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));

            } catch (TransformerException $e) {
                $this->logWarning(sprintf(
                    'Invalid article record `%d` found (Skipping): %s',
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
     * Returns an article entity
     *
     * @param array $data
     * @return Entity\Article|null
     */
    private function exportArticle(array $data)
    {
        $configuration = array(
            'oid'            => TransformerInterface::TYPE_STRING,
            'destination'    => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix' => 'article_',
                'ref'    => 'oid',
            )),
            'person'         => TransformerInterface::TYPE_STRING,
            'title'          => TransformerInterface::TYPE_STRING,
            'content'        => TransformerInterface::TYPE_STRING,
            'slug'           => TransformerInterface::TYPE_STRING,
            'language'       => TransformerInterface::TYPE_STRING,
            'end_action'     => TransformerInterface::TYPE_STRING,
            'total_rating'   => TransformerInterface::TYPE_STRING,
            'num_comments'   => TransformerInterface::TYPE_INT,
            'num_ratings'    => TransformerInterface::TYPE_INT,
            'view_count'     => TransformerInterface::TYPE_INT,
            'status'         => TransformerInterface::TYPE_STRING,
            'date_created'   => TransformerInterface::TYPE_DATE,
            'date_published' => TransformerConfiguration::create(TransformerInterface::TYPE_DATE, array(
                'null' => true,
            )),
            'date_end' => TransformerConfiguration::create(TransformerInterface::TYPE_DATE, array(
                'null' => true,
            )),
            'categories'     => TransformerInterface::TYPE_ARRAY,
            'labels'         => TransformerInterface::TYPE_ARRAY,
            'custom_fields'  => TransformerInterface::TYPE_ARRAY,
        );

        $formatted = $this->formatter->format($data, $configuration);
        $entity    = new Entity\Article();
        $entity
            ->setRawData($data)
            ->setDestination($formatted['destination'])
            ->setOid($formatted['oid'])
            ->setPersonEmail($formatted['person'])
            ->setTitle($formatted['title'])
            ->setContent($formatted['content'])
            ->setSlug($formatted['slug'])
            ->setLanguage($formatted['language'])
            ->setEndAction($formatted['end_action'])
            ->setViewCount($formatted['view_count'])
            ->setTotalRating($formatted['total_rating'])
            ->setNumComments($formatted['num_comments'])
            ->setNumRatings($formatted['num_ratings'])
            ->setStatus($formatted['status'])
            ->setDateCreated($formatted['date_created'])
            ->setDatePublished($formatted['date_published'])
            ->setDateEnd($formatted['date_end'])
        ;

        foreach ($formatted['categories'] as $category) {
            $entity->addCategory($category);
        }
        foreach ($formatted['labels'] as $label) {
            $entity->addLabel($label);
        }

        $custom_fields = $this->getCustomFieldsParser()->export($formatted['custom_fields']);
        foreach ($custom_fields as $custom_field) {
            /** @var Entity\CustomField $custom_field */
            $entity->addCustomField($custom_field);
        }

        return $entity;
    }

    /**
     * Returns record type reader config
     *
     * @return \Application\ImportBundle\Reader\Json\JsonConfig
     */
    private function getArticleReaderConfig()
    {
        return $this->getReaderConfig(Destination\DestinationInterface::ENTITY_ARTICLE_PATH);
    }
}

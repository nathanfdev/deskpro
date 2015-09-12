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
 * Class ArticleCategories
 * @package Application\ImportBundle\Generator\Exporter\Parser\Json
 */
final class ArticleCategories extends AbstractParser
{
    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_ARTICLE_CATEGORY;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        return $this->reader->getDirectoryFilesCount($this->getArticleCategoryReaderConfig());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        return $this->exportCategories($this->reader->getData($this->getArticleCategoryReaderConfig()));
    }

    /**
     * @param array $categories
     * @return Entity\Collection
     */
    private function exportCategories(array $categories)
    {
        $collection = new Entity\Collection();

        foreach ($categories as $data) {
            $this->advanceProgressBar();

            try {
                $entity = $this->exportArticleCategory($data);

                $collection->attach($entity);
                $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));

            } catch (TransformerException $e) {
                $this->logTransformerException('JSONArticleCategory', $this->getEntityType(), 'oid', $e);
            } catch (\Exception $e) {
                $this->logUnknownException('JSONArticleCategory', $this->getEntityType(), 'oid', $e, $data);
            }
        }

        return $collection;
    }

    /**
     * @param array $data
     * @return Entity\ArticleCategory
     */
    private function exportArticleCategory(array $data)
    {
        $formatted = $this->formatter->format($data, array(
            'oid'            => TransformerInterface::TYPE_STRING,
            'import_map_key' => TransformerInterface::TYPE_STRING,
            'destination'    => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix' => 'article_category_',
                'ref'    => 'oid',
            )),
            'categories'     => TransformerInterface::TYPE_ARRAY,
        ));

        $entity = new Entity\ArticleCategory();
        $entity
            ->setRawData($data)
            ->setImportMapKey($formatted['import_map_key'])
            ->setOid($formatted['oid'])
            ->setDestination($formatted['oid'])
            ->setTitle($formatted['title'])
            ->setCategories($this->exportCategories($formatted['categories']))
        ;

        return $entity;
    }

    /**
     * Returns record type reader config
     *
     * @return \Application\ImportBundle\Reader\Json\JsonConfig
     */
    private function getArticleCategoryReaderConfig()
    {
        return $this->getReaderConfig(Destination\DestinationInterface::ENTITY_ARTICLE_CATEGORY_PATH);
    }
}

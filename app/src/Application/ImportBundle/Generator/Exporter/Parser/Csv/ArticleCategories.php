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
 * Class ArticleCategories
 * @package Application\ImportBundle\Generator\Exporter\Parser\Csv
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
        return $this->getReaderCount($this->getArticleCategoryReaderConfig());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $collection = new Entity\Collection();
        $categories = $this->getReaderData($this->getArticleCategoryReaderConfig());

        foreach ($categories as $num => $data) {
            try {
                $entity = $this->exportCategory($num, $data);

                $collection->attach($entity);
                $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));
            } catch (TransformerException $e) {
                $this->logTransformerException('CSVArticleCategory', $this->getEntityType(), 'title', $e);
            } catch (\Exception $e) {
                $this->logUnknownException('CSVArticleCategory', $this->getEntityType(), 'title', $e, $data);
            }
        }

        return $collection;
    }

    /**
     * Returns an article category entity
     *
     * @param int   $num
     * @param array $data
     *
     * @return Entity\ArticleCategory
     */
    private function exportCategory($num, array $data)
    {
        $formatted = $this->formatter->format($data, array(
            'id'          => TransformerConfiguration::create(TransformerInterface::TYPE_STRING, array(
                'default' => 'num_' . $num,
            )),
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix' => 'article_category_',
                'ref'    => 'id',
            )),
            'title'       => TransformerInterface::TYPE_STRING,
            'is_book'     => TransformerInterface::TYPE_BOOLEAN,
            'is_agent'    => TransformerInterface::TYPE_BOOLEAN,
        ));

        $entity = new Entity\ArticleCategory();
        $entity
            ->setRawData($data)
            ->setOid($formatted['id'])
            ->setDestination($formatted['destination'])
            ->setTitle($formatted['title'])
            ->setAsAgent($formatted['is_agent'])
            ->setAsBook($formatted['is_book'])
        ;

        return $entity;
    }

    /**
     * Returns record type reader config
     *
     * @return \Application\ImportBundle\Reader\Csv\CsvConfig
     */
    private function getArticleCategoryReaderConfig()
    {
        return $this->getReaderConfig(self::FILE_ARTICLE_CATEGORIES);
    }
}

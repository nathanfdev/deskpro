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

namespace Application\ImportBundle\Generator\Exporter\Parser\ZenDesk;

use Application\ImportBundle\Entity;
use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Generator\Exporter\Parser\ExportCollectionConfig;

/**
 * ZenDesk article categories parser
 *
 * Class ArticleCategories
 * @package Application\ImportBundle\Generator\Exporter\Parser\ZenDesk
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
        return count($this->reader->getArticlesCategories());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $collection = $this->exportCategories();
        $sections   = $this->exportSections();

        foreach ($collection as $category) {
            foreach ($sections as $section) {
                if ($section->getDestination() == $category->getDestination()) {
                    $category->addCategory($section);
                }
            }
        }

        return $collection;
    }

    /**
     * Returns a collection of article category entities
     *
     * @return Entity\Collection|Entity\ArticleCategory[]
     */
    protected function exportCategories()
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($this->reader->getArticlesCategories())
            ->setPrefix('ZDArticleCategory')
            ->setRefColumn('id')
            ->setMethod('exportCategory')
            ->setAdvanceProgressbar(true)
        ;

        return $this->exportCollection($config);
    }

    /**
     * Returns an article category entity
     *
     * @param array $data
     * @return Entity\ArticleCategory
     */
    protected function exportCategory(array $data)
    {
        $formatted = $this->formatter->format($data, array(
            'id'           => TransformerInterface::TYPE_INT,
            'destination'  => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix' => 'article_category_',
                'ref'    => 'id',
            )),
            'name'         => TransformerInterface::TYPE_STRING,
        ));

        $entity = new Entity\ArticleCategory();
        $entity
            ->setRawData($data)
            ->setImportMapKey(DeskPROEntity\ImportMap::TYPE_ZENDESK_ARTICLE_CATEGORY)
            ->setOid($formatted['id'])
            ->setDestination($formatted['destination'])
            ->setTitle($formatted['name'])
            ->addUserGroup('everyone')
        ;

        return $entity;
    }

    /**
     * Returns a collection of article subcategory entities
     *
     * @return Entity\Collection|Entity\ArticleCategory[]
     */
    protected function exportSections()
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($this->reader->getArticlesSections())
            ->setPrefix('ZDArticleSection')
            ->setRefColumn('id')
            ->setMethod('exportSection')
        ;

        return $this->exportCollection($config);
    }

    /**
     * Returns an article subcategory entity
     *
     * @param array $data
     * @return Entity\ArticleCategory
     */
    protected function exportSection(array $data)
    {
        $formatted = $this->formatter->format($data, array(
            'id'           => TransformerInterface::TYPE_INT,
            'destination'  => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix' => 'article_category_',
                'ref'    => 'category_id',
            )),
            'name'         => TransformerInterface::TYPE_STRING,
            'category_id'  => TransformerInterface::TYPE_INT,
        ));

        $entity = new Entity\ArticleCategory();
        $entity
            ->setRawData($data)
            ->setOid($formatted['id'])
            ->setDestination($formatted['destination'])
            ->setTitle($formatted['name'])
            ->addUserGroup('everyone')
        ;

        return $entity;
    }
}

<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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

namespace Application\ImportBundle\Generator\Writer\DeskPRO\Importer;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Entity;

/**
 * Class ArticleCategory
 * @package Application\ImportBundle\Generator\Writer\DeskPRO\Importer
 */
final class ArticleCategory extends AbstractImporter
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
    public function getDoctrineEntities(Entity\EntityInterface $entity, $entity_id = null)
    {
        if ( ! $entity instanceof Entity\ArticleCategory) {
            Entity\UnexpectedException::throwUnexpectedEntityTypeException($entity);
        }

        $category = $this->findOrCreateArticleCategory($entity_id);
        $category->setRealTitle($entity->getTitle());

        $this->createDeepCategories($category, $entity);

        $this->records->setPrimaryEntity($category);
        return $this->records;
    }

    /**
     * Create categories tree
     *
     * @param DeskPROEntity\ArticleCategory $root_category
     * @param Entity\ArticleCategory        $entity
     */
    private function createDeepCategories(DeskPROEntity\ArticleCategory $root_category, Entity\ArticleCategory $entity)
    {
        foreach ($entity->getCategories() as $child_entity) {
            $category = new DeskPROEntity\ArticleCategory();
            $category
                ->setRealTitle($child_entity->getTitle())
                ->setParent($root_category)
            ;

            $this->createDeepCategories($category, $child_entity);
            $this->records->addRelatedEntity($category);
        }
    }

    /**
     * Returns an article category by oid
     * Creates a new article if not found
     *
     * @param int $entity_id
     *
     * @return DeskPROEntity\ArticleCategory
     */
    protected function findOrCreateArticleCategory($entity_id)
    {
        $category = $this->getArticleCategoryMapper()->findOneBy(array('id' => $entity_id), false);
        if ($category) {
            $this->logDebug(sprintf('Found existing article category `%s`', $category->getRealTitle()));
            return $category;
        }

        $this->logDebug('Creating new article category');
        return new DeskPROEntity\ArticleCategory();
    }
}

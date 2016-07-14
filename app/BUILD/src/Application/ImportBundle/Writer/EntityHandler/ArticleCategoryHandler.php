<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace Application\ImportBundle\Writer\EntityHandler;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Model;
use Application\ImportBundle\Writer\Mapper\MapperRegistry;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;

/**
 * Article category importer.
 * 
 * Class ArticleCategory
 */
class ArticleCategoryHandler extends AbstractEntityHandler
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param MapperRegistry  $mappers
     * @param EntityManager   $em
     * @param LoggerInterface $logger
     */
    public function __construct(MapperRegistry $mappers, LoggerInterface $logger, EntityManager $em)
    {
        parent::__construct($mappers, $logger);
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public static function getModelClass()
    {
        return Model\ArticleCategory::class;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(Model\ImportModelInterface $model, $entityId = null)
    {
        if (!$model instanceof Model\ArticleCategory) {
            Model\UnexpectedException::throwUnexpectedEntityTypeException($model);
        }

        $category = $this->findOrCreateArticleCategory($entityId);

        $this->setCategoryProperties($model, $category, null);
        $this->createOrUpdateDeepCategories($category, $model);

        $this->records->setPrimaryEntity($category);
    }

    /**
     * Create categories tree.
     *
     * @param DeskPROEntity\ArticleCategory $parent_category
     * @param Model\ArticleCategory         $entity
     */
    private function createOrUpdateDeepCategories(DeskPROEntity\ArticleCategory $parent_category, Model\ArticleCategory $entity)
    {
        $new_categories = [];
        $old_categories = [];

        foreach ($entity->getCategories() as $new_category) {
            $new_categories[$new_category->getTitle()] = $new_category;
        }
        foreach ($parent_category->getChildren() as $old_category) {
            $old_categories[$old_category->getRealTitle()] = $old_category;
        }

        foreach ($parent_category->getChildren() as $old_category) {
            $title        = $old_category->getRealTitle();
            $new_category = isset($new_categories[$title]) ? $new_categories[$title] : null;

            if ($new_category) {
                $this->setCategoryProperties($new_category, $old_category, $parent_category);
                $this->createOrUpdateDeepCategories($old_category, $new_category);

                $this->logger->debug(sprintf('Updating article category `%s`', $title));
            } else {
                $this->em->remove($old_category);
                $this->logger->debug(sprintf('Removing article category `%s`', $title));
            }
        }

        foreach ($entity->getCategories() as $child_entity) {
            if (isset($old_categories[$child_entity->getTitle()])) {
                continue;
            }

            $category = $this->setCategoryProperties($child_entity, new DeskPROEntity\ArticleCategory(), $parent_category);

            $this->createOrUpdateDeepCategories($category, $child_entity);
            $this->records->addRelatedEntity($category);
        }
    }

    /**
     * Set article category properties.
     *
     * @param Model\ArticleCategory              $entity
     * @param DeskPROEntity\ArticleCategory      $category
     * @param DeskPROEntity\ArticleCategory|null $parent_category
     *
     * @return DeskPROEntity\ArticleCategory
     */
    private function setCategoryProperties(Model\ArticleCategory $entity, DeskPROEntity\ArticleCategory $category, DeskPROEntity\ArticleCategory $parent_category = null)
    {
        $category
            ->setRealTitle($entity->getTitle())
            ->setParent($parent_category)
            ->setIsAgent($entity->isAgent())
            ->setIsBook($entity->isBook())
            ->resetUserGroups()
        ;

        foreach ($entity->getUserGroups() as $user_group_name) {
            $user_group = $this->findUserGroup($user_group_name);
            if ($user_group) {
                $category->addUsergroup($user_group);
            }
        }

        return $category;
    }

    /**
     * Returns an article category by oid.
     * Creates a new article if not found.
     *
     * @param int $entity_id
     *
     * @return DeskPROEntity\ArticleCategory
     */
    protected function findOrCreateArticleCategory($entity_id)
    {
        $category = $this->mappers->getArticleCategoryMapper()->findOneBy(['id' => $entity_id], false);
        if ($category) {
            $this->logger->debug(sprintf('Found existing article category `%s`', $category->getRealTitle()));

            return $category;
        }

        $this->logger->debug('Creating new article category');

        return new DeskPROEntity\ArticleCategory();
    }
}

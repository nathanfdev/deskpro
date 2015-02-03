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

namespace Application\ImportBundle\Generator\Writer\DeskPro\Importer;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * DeskPro article importer
 *
 * Class Article
 * @package Application\ImportBundle\Generator\Writer\DeskPro\Importer
 */
final class Article extends AbstractImporter
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
     *
     * todo add referred objects
     * $record['total_rating'] = $kbval->total_rating;
     * $record['num_comments'] = $kbval->num_comments;
     * $record['num_ratings']  = $kbval->num_ratings;
     *
     * @var Entity\Article $importing_entity
     */
    public function getDoctrineEntities(Entity\EntityInterface $importing_entity)
    {
        $this->records = new ArrayCollection();
        $exist_article = $this->getArticleMapper()->findOneByTitle($importing_entity->getTitle(), false);
        if ($exist_article) {
            $this->logWarning(sprintf(
                'An Article with the title `%s` already exists (skipping)',
                $importing_entity->getTitle()
            ));
        } else {
            $article = new DeskPROEntity\Article();
            $article
                ->setTitle($importing_entity->getTitle())
                ->setContent($importing_entity->getContent())
                ->setSlug($importing_entity->getSlug())
                ->setStatus($importing_entity->getStatus())
                ->setLanguage($this->findLanguage($importing_entity->getLanguage()))
                ->setDateCreated($importing_entity->getDateCreated())
                ->setDatePublished($importing_entity->getDatePublished())
                ->setDateEnd($importing_entity->getDateEnd())
                ->setPerson($this->getPersonMapper()->findOneByEmail($importing_entity->getPersonEmail()));

            foreach ($importing_entity->getCategories() as $category) {
                $article->addToCategory($this->findOrCreateArticleCategory($category));
            }
            foreach ($importing_entity->getLabels() as $label) {
                $article->addLabel($this->createArticleLabel($label));
            }

            $this->records->add($article);
        }

        return $this->records;
    }

    /**
     * Returns an article category by title
     * Creates a new article category if not found
     *
     * @param string $title
     *
     * @return DeskPROEntity\ArticleCategory|null
     * @throws \Exception
     */
    private function findOrCreateArticleCategory($title)
    {
        $category = null;
        if ($title) {
            $category = $this->getArticleCategoryMapper()->findOneByTitle($title, false);
            if ($category) {
                $this->logInfo(sprintf('Found existing article category `%s`', $category->getTitle()));
            } else {
                $category = new DeskPROEntity\ArticleCategory();
                $category->setRealTitle($title);

                $this->records->add($category);
                $this->logWarning(sprintf('New article category creating `%s`', $category->getTitle()));
            }
        }

        return $category;
    }

    /**
     * Returns a new article label entity
     *
     * @param string $label
     * @return DeskPROEntity\LabelArticle
     */
    private function createArticleLabel($label)
    {
        $entity = new DeskPROEntity\LabelArticle();
        $entity->setLabel($label);

        $this->records->add($entity);
        return $entity;
    }

    /**
     * Returns the article mapper
     *
     * @return Mapper\Article
     * @throws \Exception
     */
    private function getArticleMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_ARTICLE);
    }

    /**
     * Returns the article category mapper
     *
     * @return Mapper\ArticleCategory
     * @throws \Exception
     */
    private function getArticleCategoryMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_ARTICLE_CATEGORY);
    }
}

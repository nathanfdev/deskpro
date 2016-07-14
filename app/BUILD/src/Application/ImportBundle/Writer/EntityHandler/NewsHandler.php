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
use Application\ImportBundle\Writer\Helper\LabelHelper;

/**
 * DeskPRO news importer.
 *
 * Class News
 */
class NewsHandler extends AbstractEntityHandler
{
    /**
     * {@inheritdoc}
     */
    public static function getModelClass()
    {
        return Model\News::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param Model\News $model
     */
    public function prepare(Model\ImportModelInterface $model, $entityId = null)
    {
        $entity = $this->mappers->getNewsMapper()->findOneByTitle($model->getTitle(), false) ?: new DeskPROEntity\News();
        $entity
            ->setTitle($model->getTitle())
            ->setContent($model->getContent())
            ->setPerson($this->mappers->getPersonMapper()->findOneByEmail($model->getPerson()))
            ->setLanguage($this->findLanguage($model->getLanguage()))
            ->setCategory($this->findOrCreateNewsCategory($model->getCategory()))
            ->setDateCreated($model->getDateCreated())
            ->setDatePublished($model->getDatePublished())
            ->setViewCount($model->getViewCount())
        ;

        $labelsHelper = new LabelHelper($this->logger);
        $labelsHelper->updateLabels($model, $entity, DeskPROEntity\LabelNews::class);

        $this->records->setPrimaryEntity($entity);
    }

    /**
     * Returns an feedback category by title
     * Creates a new feedback category if not found.
     *
     * @param string $title
     *
     * @return DeskPROEntity\NewsCategory|null
     */
    private function findOrCreateNewsCategory($title)
    {
        $category = null;
        if ($title) {
            $category = $this->mappers->getNewsCategoryMapper()->findOneByTitle($title, false);
            if ($category) {
                $this->logger->debug(sprintf('Found existing news category `%s`', $category->getTitle()));
            } else {
                $category = new DeskPROEntity\NewsCategory();
                $category->setRealTitle($title);

                $this->records->addRelatedEntity($category);
                $this->logger->info(sprintf('New news category creating `%s`', $category->getTitle()));
            }
        }

        return $category;
    }
}

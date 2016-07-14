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

namespace Application\ImportBundle\Writer\Mapper;

use Application\DeskPRO\Entity\ArticleCategory;

/**
 * Article category record mapper.
 *
 * Class ArticleCategory
 */
class ArticleCategoryMapper extends AbstractEntityManagerMapper implements MapperByTitleInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getEntityClass()
    {
        return ArticleCategory::class;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByTitle($title, $throw_exception = true)
    {
        return $this->findDeepCategory($title, null, $throw_exception);
    }

    /**
     * Find a category
     * We store value for categories like "A > A1".
     *
     * Category 1
     *   SubCategory A
     *   SubCategory B
     *
     * @param array|string         $category_path
     * @param ArticleCategory|null $parent
     * @param bool                 $throw_exception
     *
     * @return ArticleCategoryMapper
     */
    public function findDeepCategory($category_path, ArticleCategory $parent = null, $throw_exception = true)
    {
        if (is_string($category_path)) {
            $category_path = explode('>', $category_path);
            $category_path = array_map('trim', $category_path);
        }

        $category = $this->findOneBy(
            [
                'title'  => array_shift($category_path),
                'parent' => $parent ? $parent->getId() : null,
            ],
            $throw_exception
        );

        return empty($category_path) ? $category : $this->findDeepCategory($category_path, $category, $throw_exception);
    }
}

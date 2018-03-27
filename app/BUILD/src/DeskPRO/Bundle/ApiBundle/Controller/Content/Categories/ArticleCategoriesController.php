<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Content\Categories;

use Application\DeskPRO\Entity\ArticleCategory;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\Content\Categories\ArticleCategoryType;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class ArticleCategoriesController.
 *
 * @ApiModes("all")
 * @Rest\Route("/article_categories")
 * @ApiDoc(target="all", section="Content", output="DeskPRO\Bundle\AppBundle\Serializer\Model\Content\Categories\ArticleCategory")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Content\Categories\ArticleCategoryType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\ArticleCategory"
 *      }
 *     }
 * )
 */
class ArticleCategoriesController extends AbstractCategoriesController
{
    public static $entity = ArticleCategory::class;
    public static $type   = ArticleCategoryType::class;
}

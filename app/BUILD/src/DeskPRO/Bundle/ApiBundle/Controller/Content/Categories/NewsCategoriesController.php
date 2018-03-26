<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Content\Categories;

use Application\DeskPRO\Entity\NewsCategory;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\Content\Categories\NewsCategoryType;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class NewsCategoriesController.
 *
 * @ApiModes("all")
 * @Rest\Route("/news_categories")
 * @ApiDoc(target="all", section="Content", output="DeskPRO\Bundle\AppBundle\Serializer\Model\Content\Categories\NewsCategory")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Content\Categories\NewsCategoryType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\NewsCategory"
 *      }
 *     }
 * )
 */
class NewsCategoriesController extends AbstractCategoriesController
{
    public static $entity = NewsCategory::class;
    public static $type   = NewsCategoryType::class;
}

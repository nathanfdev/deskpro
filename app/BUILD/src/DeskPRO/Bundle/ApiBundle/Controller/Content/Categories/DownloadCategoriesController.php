<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Content\Categories;

use Application\DeskPRO\Entity\DownloadCategory;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\Content\Categories\DownloadCategoryType;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class DownloadCategoriesController.
 *
 * @ApiModes("all")
 * @Rest\Route("/download_categories")
 * @ApiDoc(target="all", section="Content", output="DeskPRO\Bundle\AppBundle\Serializer\Model\Content\Categories\DownloadCategory")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Content\Categories\DownloadCategoryType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\DownloadCategory"
 *      }
 *     }
 * )
 */
class DownloadCategoriesController extends AbstractCategoriesController
{
    public static $entity = DownloadCategory::class;
    public static $type   = DownloadCategoryType::class;
}

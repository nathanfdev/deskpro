<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Content\Categories;

use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\DownloadCategory;
use Application\DeskPRO\Entity\NewsCategory;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Content\CategoriesList;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

/**
 * Class CategoriesController.
 *
 * @ApiModes("all")
 * @Rest\Route("/content_categories")
 */
class CategoriesController extends BaseController
{
    /**
     * @ApiDoc(
     *     section="Content",
     *     resourceDescription="Operations about content",
     *     description="Get categories for articles, news and downloads",
     *     statusCodes={
     *         200="Returned if request was successful"
     *     },
     *     output="DeskPRO\Bundle\AppBundle\Serializer\Model\Content\CategoriesList",
     * )
     * @Rest\View(serializerGroups={"list"})
     * @Rest\Get("")
     */
    public function getCategoriesGroupedByContentTypeAction()
    {
        $list = new CategoriesList(
            $this->getRepository(ArticleCategory::class)->findAll(),
            $this->getRepository(NewsCategory::class)->findAll(),
            $this->getRepository(DownloadCategory::class)->findAll()
        );

        return View::create($this->wrap($list));
    }
}

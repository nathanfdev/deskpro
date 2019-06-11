<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Content;

use Application\DeskPRO\Entity\CustomDefArticle;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\AbstractCustomFieldsController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class ArticleCustomFieldsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/article_custom_fields")
 * @ApiDoc(target="all", section="Tickets", output="Application\DeskPRO\Entity\CustomDefArticle")
 * @ApiDoc(
 *     target="postAction, putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomFieldType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\CustomDefArticle"
 *      }
 *     }
 * )
 */
class ArticleCustomFieldsController extends AbstractCustomFieldsController
{
    public static $entity = CustomDefArticle::class;
}

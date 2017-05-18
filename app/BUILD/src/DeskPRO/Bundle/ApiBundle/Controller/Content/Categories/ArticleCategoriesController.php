<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

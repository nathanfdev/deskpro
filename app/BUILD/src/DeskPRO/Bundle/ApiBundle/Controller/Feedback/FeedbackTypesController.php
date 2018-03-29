<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Feedback;

use Application\DeskPRO\Entity\FeedbackCategory;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * API access to feedback types.
 *
 * **Note that current model called as Category, so don't be fooled with this - it's type**
 *
 * @ApiModes("all")
 * @Rest\Route("/feedback_types")
 * @ApiDoc(target="all", section="Feedback", output="Application\DeskPRO\Entity\FeedbackCategory")
 */
class FeedbackTypesController extends CrudController
{
    public static $exposeOnly = ['get', 'list', 'count'];
    public static $entity     = FeedbackCategory::class;
    public static $listSort   = 'title';
    public static $listOrder  = 'asc';
}

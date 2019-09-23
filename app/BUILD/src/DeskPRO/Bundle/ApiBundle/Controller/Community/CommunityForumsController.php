<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Community;

use Application\DeskPRO\Entity\CommunityForum;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * API access to community forums.
 *
 * @ApiModes("all")
 * @Rest\Route("/community_forums")
 * @ApiDoc(target="all", section="Community", output="Application\DeskPRO\Entity\CommunityForum")
 */
class CommunityForumsController extends CrudController
{
    public static $exposeOnly = ['get', 'list', 'count'];
    public static $entity     = CommunityForum::class;
    public static $listSort   = 'title';
    public static $listOrder  = 'asc';
}

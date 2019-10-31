<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Community;

use Application\DeskPRO\Entity\CommunityChannel;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * API access to community channels.
 *
 * @ApiModes("all")
 * @Rest\Route("/community_channels")
 * @ApiDoc(target="all", section="Community", output="Application\DeskPRO\Entity\CommunityChannel")
 */
class CommunityChannelsController extends CrudController
{
    public static $exposeOnly = ['get', 'list', 'count'];
    public static $entity     = CommunityChannel::class;
    public static $listSort   = 'title';
    public static $listOrder  = 'asc';
}

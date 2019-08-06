<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\MassActions;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\MassActions\CommunityTopicComment\CommunityTopicCommentMassActionsType;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class CommunityTopicCommentMassActionsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/mass_actions/community_topic_comments")
 * @ApiDoc(target="all", section="Mass actions")
 * @ApiDoc(
 *     target="massAction",
 *     input="DeskPRO\Bundle\AppBundle\Form\Type\MassActions\CommunityTopicComment\CommunityTopicCommentMassActionsType"
 * )
 */
class CommunityTopicCommentMassActionsController extends AbstractMassActionsController
{
    protected static $type = CommunityTopicCommentMassActionsType::class;
}

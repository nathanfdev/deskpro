<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\MassActions;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\MassActions\CommunityTopic\CommunityTopicMassActionsType;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class CommunityTopicMassActionsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/mass_actions/community_topic")
 * @ApiDoc(target="all", section="Mass actions")
 * @ApiDoc(
 *     target="massAction",
 *     input="DeskPRO\Bundle\AppBundle\Form\Type\MassActions\CommunityTopic\CommunityTopicMassActionsType"
 * )
 */
class CommunityTopicMassActionsController extends AbstractMassActionsController
{
    protected static $type = CommunityTopicMassActionsType::class;
}

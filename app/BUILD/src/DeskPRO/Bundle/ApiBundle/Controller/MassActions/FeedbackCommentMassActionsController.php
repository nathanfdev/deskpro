<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\MassActions;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\MassActions\FeedbackComment\FeedbackCommentMassActionsType;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class FeedbackCommentMassActionsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/mass_actions/feedback_comments")
 * @ApiDoc(target="all", section="Mass actions")
 * @ApiDoc(
 *     target="massAction",
 *     input="DeskPRO\Bundle\AppBundle\Form\Type\MassActions\FeedbackComment\FeedbackCommentMassActionsType"
 * )
 */
class FeedbackCommentMassActionsController extends AbstractMassActionsController
{
    protected static $type = FeedbackCommentMassActionsType::class;
}

<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\MassActions;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\MassActions\Feedback\FeedbackMassActionsType;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class FeedbackMassActionsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/mass_actions/feedback")
 * @ApiDoc(target="all", section="Mass actions")
 * @ApiDoc(
 *     target="massAction",
 *     input="DeskPRO\Bundle\AppBundle\Form\Type\MassActions\Feedback\FeedbackMassActionsType"
 * )
 */
class FeedbackMassActionsController extends AbstractMassActionsController
{
    protected static $type = FeedbackMassActionsType::class;
}

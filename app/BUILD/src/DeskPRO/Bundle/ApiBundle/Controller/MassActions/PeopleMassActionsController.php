<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\MassActions;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\MassActions\People\PersonMassActionsType;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class PeopleMassActionsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/mass_actions/people")
 * @ApiDoc(target="all", section="Mass actions")
 * @ApiDoc(
 *     target="massAction",
 *     input="DeskPRO\Bundle\AppBundle\Form\Type\MassActions\CommunityTopic\PersonMassActionsType"
 * )
 */
class PeopleMassActionsController extends AbstractMassActionsController
{
    protected static $type = PersonMassActionsType::class;
}

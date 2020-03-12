<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\MassActions;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\MassActions\Organizations\OrganizationMassActionsType;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class OrganizationMassActionsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/mass_actions/organizations")
 * @ApiDoc(target="all", section="Mass actions")
 * @ApiDoc(
 *     target="massAction",
 *     input="DeskPRO\Bundle\AppBundle\Form\Type\MassActions\CommunityTopic\OrganizationMassActionsType"
 * )
 */
class OrganizationMassActionsController extends AbstractMassActionsController
{
    protected static $type = OrganizationMassActionsType::class;
}

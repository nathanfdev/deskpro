<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Voice;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAutoAttendant;
use DeskPRO\Bundle\AppBundle\Form\Type\Voice\VoiceAutoAttendantType;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class VoiceAutoAttendantsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/voice_auto_attendants")
 * @Feature("voice")
 * @ApiDoc(target="all", section="Voice Channel", output="DeskPRO\Bundle\AppBundle\Entity\VoiceAutoAttendant")
 * @ApiUserContext("admin")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Voice\VoiceAutoAttendantType",
 *      "options"={
 *          "data"="DeskPRO\Bundle\AppBundle\Entity\VoiceAutoAttendant"
 *      }
 *     }
 * )
 */
class VoiceAutoAttendantsController extends CrudController
{
    public static $entity       = VoiceAutoAttendant::class;
    public static $type         = VoiceAutoAttendantType::class;
    public static $listPaginate = false;
}

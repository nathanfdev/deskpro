<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Voice;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\VoiceNumber;
use DeskPRO\Bundle\AppBundle\Form\Type\Voice\VoiceNumberType;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class VoiceNumbersController.
 *
 * @ApiModes("all")
 * @Rest\Route("/voice_numbers")
 * @Feature("voice")
 * @ApiDoc(target="all", section="Voice Channel", output="DeskPRO\Bundle\AppBundle\Entity\VoiceNumber")
 * @ApiUserContext("admin", agent={"list", "get", "count"})
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Voice\VoiceNumberType",
 *      "options"={
 *          "data"="DeskPRO\Bundle\AppBundle\Entity\VoiceNumber"
 *      }
 *     }
 * )
 */
class VoiceNumbersController extends AbstractVoiceCrudController
{
    public static $entity       = VoiceNumber::class;
    public static $type         = VoiceNumberType::class;
    public static $listPaginate = false;
}

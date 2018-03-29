<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Voice;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class VoicePhoneCallsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/voice_phone_calls")
 * @Feature("voice")
 * @ApiDoc(target="all", section="Voice Channel", output="DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall")
 */
class VoicePhoneCallsController extends CrudController
{
    public static $entity     = VoicePhoneCall::class;
    public static $exposeOnly = ['get', 'list', 'count'];
}

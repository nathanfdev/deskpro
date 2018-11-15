<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Voice;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\AbstractVoiceAccount;
use DeskPRO\Bundle\VoiceBundle\Form\Type\VoiceAccountType;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class VoiceAccountsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/voice_accounts")
 * @Feature("voice")
 * @ApiUserContext("admin", agent={"list", "get", "count"})
 * @ApiDoc(target="all", section="Voice Channel", output="DeskPRO\Bundle\AppBundle\Entity\AbstractVoiceAccount")
 */
class VoiceAccountsController extends AbstractVoiceCrudController
{
    public static $entity       = AbstractVoiceAccount::class;
    public static $type         = VoiceAccountType::class;
    public static $listOrder    = 'asc';
    public static $listPaginate = false;
    public static $exposeOnly   = ['get', 'list', 'count', 'delete'];
}

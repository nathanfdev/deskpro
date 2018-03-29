<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Settings;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

/**
 * Class UsersourceSettingsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/settings/user_source")
 */
class UsersourceSettingsController extends BaseController
{
    /**
     * @ApiDoc(
     *     section="Usersource settings",
     *     description="Get usersource settings",
     *     statusCodes={
     *         200="Success"
     *     },
     *     output={
     *          "class"="DeskPRO\Bundle\AppBundle\Settings\Model\UsersourceSettings"
     *      }
     * )
     *
     * @Rest\Get("")
     *
     * @return View
     */
    public function getAction()
    {
        return new View($this->wrap($this->get('usersource_settings_resolver')->getSettings()));
    }
}

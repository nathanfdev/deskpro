<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Authentication\OAuth;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Entity\OAuthClient;
use DeskPRO\Bundle\AppBundle\Form\Type\OAuth\OAuthClientType;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class OAuthClientsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/oauth_clients")
 * @ApiUserContext("admin")
 * @ApiDoc(target="all", section="Auth", output="DeskPRO\Bundle\AppBundle\Entity\OAuthClient")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *       "class"="DeskPRO\Bundle\AppBundle\Form\Type\OAuth\OAuthClientType",
 *       "options"={
 *          "data"="DeskPRO\Bundle\AppBundle\Entity\OAuthClient"
 *       }
 *     }
 * )
 */
class OAuthClientsController extends CrudController
{
    public static $entity       = OAuthClient::class;
    public static $type         = OAuthClientType::class;
    public static $listPaginate = false;
}

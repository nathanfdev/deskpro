<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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

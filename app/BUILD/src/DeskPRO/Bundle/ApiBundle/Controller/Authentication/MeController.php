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

namespace DeskPRO\Bundle\ApiBundle\Controller\Authentication;

use Application\DeskPRO\Entity\TmpData;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Model\DeviceSetupToken;
use DeskPRO\Bundle\ApiBundle\Model\Me;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class MeController.
 *
 * @ApiModes("all")
 * @Rest\Route("/me")
 */
class MeController extends BaseController
{
    /**
     * Gather specific info about authentication.
     *
     * @ApiDoc(
     *     section="Auth",
     *     description="get information about the authenticated user",
     *     output="DeskPRO\Bundle\ApiBundle\Model\Me",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     output="DeskPRO\Bundle\ApiBundle\Model\Me"
     * )
     * @ApiUserContext("user")
     * @Rest\Get("")
     */
    public function meAction()
    {
        $token      = $this->get('security.token_storage')->getToken();
        $clientInfo = $this->get('api_client_info');

        return View::create($this->wrap(new Me($token, $clientInfo, 2)));
    }

    /**
     * Get device setup token.
     *
     * @ApiDoc(
     *     section="Auth",
     *     description="get my profile action",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     output="DeskPRO\Bundle\ApiBundle\Model\DeviceSetupToken"
     * )
     *
     * @Rest\Get("/device-setup-token")
     */
    public function getDeviceSetupTokenAction()
    {
        $tmpData = TmpData::create('device_setup_token', ['agent_id' => $this->getUser()->getId()], '+10 minutes');
        $this->getManager()->persist($tmpData);
        $this->getManager()->flush();

        $url = $this->generateUrl(
            'api_authenticate_device',
            ['auth' => $tmpData->getAuth()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return View::create($this->wrap(new DeviceSetupToken($url)));
    }
}

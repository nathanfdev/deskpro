<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\ApiBundle\Controller\Helpdesk;

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Model\PrimitiveArray;
use DeskPRO\Bundle\AppBundle\CountBadge\Count;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DiscoveryController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="Used by apps to detect that this is a real helpdesk",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Get("/helpdesk/discover", name="api_helpdesk_discover")
     */
    public function discoverAction(Request $request)
    {
        //$brand = $this->get('brand_stack')->getActive();
        //$helpdesk_url = rtrim($brand->getSetting('core.deskpro_url'), '/') . '/';

        $s = $this->get('deskpro.core.settings');
        $helpdesk_url = rtrim($s->get('core.deskpro_url'), '/') . '/';

        $base_api_url = $helpdesk_url . 'api/v2/';

        $ret = [
            'is_deskpro'   => true,
            'helpdesk_url' => $helpdesk_url,
            'base_api_url' => $base_api_url,
            'build'        => DP_BUILD_TIME,
        ];

        return View::create(
            $this->dataSerialize(new PrimitiveArray($ret)),
            Response::HTTP_OK
        );
    }
}

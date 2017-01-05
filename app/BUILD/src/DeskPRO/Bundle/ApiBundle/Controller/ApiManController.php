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

namespace DeskPRO\Bundle\ApiBundle\Controller;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class ApiManController.
 *
 * @ApiUserContext("open")
 */
class ApiManController extends BaseController
{
    /**
     * @Rest\Get("/", name="api_landing")
     */
    public function v2LandingAction(Request $request)
    {
        // This is LegacyApiBundle:Docs:about
        return new RedirectResponse($request->getUriForPath('/api'));
    }

    /**
     * @Rest\Get("/man", name="api_man")
     */
    public function manAction(Request $request)
    {
        return new RedirectResponse('http://api.deskpro.com/');
    }

    /**
     * @Rest\Get("/doc", name="api_doc")
     */
    public function docAction(Request $request, $view = ApiDoc::DEFAULT_VIEW)
    {
        $subRequest = $request->duplicate($request->query->all(), null, [
            '_controller' => 'NelmioApiDocBundle:ApiDoc:index', 'view' => $view,
        ]);

        return $this->getKernel()->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }
}

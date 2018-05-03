<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Controller\LowLevel;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GoController extends BaseController
{
    /**
     * @Route("/dp/go/deskpro/third-party-list")
     * @Method("GET")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function goThirdPartyListAction(Request $request)
    {
        $url = $this->get('assets.packages')->getUrl('/docs/third-party/index.html', 'assets_root');

        return new RedirectResponse($url, 302);
    }
}

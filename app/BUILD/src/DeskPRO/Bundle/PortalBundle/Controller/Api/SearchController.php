<?php

namespace DeskPRO\Bundle\PortalBundle\Controller\Api;

use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SearchController.
 */
class SearchController extends AbstractApiController
{
    /**
     * @Route("/portal/api/search/similar/{content_type}", name="portal_api_search_similar", defaults={"content_type":null})
     * @Method({"GET"})
     *
     * @param Request $request
     * @param string  $content_type
     *
     * @return View
     */
    public function similarTicketsAction(Request $request, $content_type = null)
    {
        $path  = ['content_type' => $content_type];
        $query = ['content' => $request->get('content', '')];

        return $this->forward('PortalBundle:Search:similarTo', $path, $query);
    }
}

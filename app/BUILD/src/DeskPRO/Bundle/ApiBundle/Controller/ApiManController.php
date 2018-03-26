<?php

namespace DeskPRO\Bundle\ApiBundle\Controller;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class ApiManController.
 *
 * @ApiUserContext("open")
 */
class ApiManController extends BaseController
{
    /**
     * @Rest\Get("/")
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function v2LandingAction(Request $request)
    {
        // This is LegacyApiBundle:Docs:about
        return new RedirectResponse($request->getUriForPath('/api'));
    }

    /**
     * @Rest\Get("/man")
     *
     * @return RedirectResponse
     */
    public function manAction()
    {
        return new RedirectResponse('http://api.deskpro.com/');
    }

    /**
     * @Rest\Get("/doc")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function docAction(Request $request)
    {
        $subRequest = $request->duplicate($request->query->all(), null, [
            '_controller' => 'NelmioApiDocBundle:ApiDoc:index', 'view' => ApiDoc::DEFAULT_VIEW,
        ]);

        return $this->getKernel()->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }
}

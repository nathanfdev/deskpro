<?php

namespace DeskPRO\Bundle\PortalBundle\Controller\Api\PortalDesigner;

use DeskPRO\Bundle\AppBundle\Entity\ThemeSetAsset;
use DeskPRO\Bundle\PortalBundle\Controller\Api\AbstractApiController;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AssetsController.
 */
class AssetsController extends AbstractApiController
{
    use HelperMethods;

    /**
     * @Route("/portal/api/style/edit-theme-set/assets")
     * @Method({"GET"})
     */
    public function listEditThemeSetAssetsAction()
    {
        return $this->wrap($this->getAssetsManager()->getEditThemeSetAssets());
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/assets")
     * @Method({"POST"})
     *
     * @param Request $request
     *
     * @return View
     */
    public function uploadEditThemeSetAssetAction(Request $request)
    {
        return $this->wrap($this->getAssetsManager()->uploadEditThemeSetAsset($request->files->get('file')));
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/assets/{id}", requirements={"id"="\d+"})
     * @Method({"DELETE"})
     * @ParamConverter("asset", class="AppBundle:ThemeSetAsset")
     *
     * @param ThemeSetAsset $asset
     *
     * @return View
     */
    public function deleteEditThemeSetAssetAction(ThemeSetAsset $asset)
    {
        $this->getAssetsManager()->deleteEditThemeSetAsset($asset);

        return new Response(null, Response::HTTP_OK);
    }
}

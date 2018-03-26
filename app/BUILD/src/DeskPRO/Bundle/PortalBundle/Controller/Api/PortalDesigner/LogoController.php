<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Controller\Api\PortalDesigner;

use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\PortalBundle\Controller\Api\AbstractApiController;
use DeskPRO\Bundle\PortalBundle\Designer\AssetsManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class LogoController.
 */
class LogoController extends AbstractApiController
{
    use HelperMethods;

    /**
     * @Route("/portal/api/style/edit-theme-set/logo")
     * @Method({"POST"})
     *
     * @param Request $request
     *
     * @return ApiWrapper
     */
    public function uploadLogoAction(Request $request)
    {
        return $this->wrap(
            $this->getAssetsManager()->uploadBlob($request->files->get('file'), AssetsManager::CUSTOM_LOGO_TAG)
        );
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/favicon")
     * @Method({"POST"})
     *
     * @param Request $request
     *
     * @return ApiWrapper
     */
    public function uploadFaviconAction(Request $request)
    {
        return $this->wrap(
            $this->getAssetsManager()->uploadBlob($request->files->get('file'), AssetsManager::CUSTOM_LOGO_TAG)
        );
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/logo")
     * @Method({"GET"})
     *
     * @return ApiWrapper
     */
    public function getCustomLogoUrlAction()
    {
        return $this->wrap($this->getAssetsManager()->getEditThemeSetBlobAsset(AssetsManager::CUSTOM_LOGO_TAG));
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/favicon")
     * @Method({"GET"})
     *
     * @return ApiWrapper
     */
    public function getCustomFaviconUrlAction()
    {
        return $this->wrap($this->getAssetsManager()->getEditThemeSetBlobAsset(AssetsManager::CUSTOM_FAVICON_TAG));
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/logo")
     * @Method({"DELETE"})
     *
     * @return ApiWrapper
     */
    public function deleteEditThemeSetLogoAssetAction()
    {
        return $this->wrap($this->getAssetsManager()->deleteEditThemeSetAsset(
            $this->getAssetsManager()->getEditThemeSetBlobAsset(AssetsManager::CUSTOM_LOGO_TAG)
        ));
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/favicon")
     * @Method({"DELETE"})
     *
     * @return ApiWrapper
     */
    public function deleteEditThemeSetFaviconAssetAction()
    {
        return $this->wrap($this->getAssetsManager()->deleteEditThemeSetAsset(
            $this->getAssetsManager()->getEditThemeSetBlobAsset(AssetsManager::CUSTOM_FAVICON_TAG)
        ));
    }
}

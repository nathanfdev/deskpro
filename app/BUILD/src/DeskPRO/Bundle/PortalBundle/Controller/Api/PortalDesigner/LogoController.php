<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Controller\Api\PortalDesigner;

use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\PortalBundle\Controller\Api\AbstractApiController;
use DeskPRO\Bundle\PortalBundle\Designer\AssetsManager;
use GuzzleHttp\Client;
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
            $this->getAssetsManager()->uploadBlob($request->files->get('file'), AssetsManager::CUSTOM_FAVICON_TAG)
        );
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/splash_image")
     * @Method({"POST"})
     *
     * @param Request $request
     *
     * @return ApiWrapper
     */
    public function uploadSplashImageAction(Request $request)
    {
        return $this->wrap(
            $this->getAssetsManager()->uploadBlob($request->files->get('file'), AssetsManager::CUSTOM_SPLASH_IMAGE_TAG)
        );
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/unsplash")
     * @Method({"POST"})
     *
     * @param Request $request
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return ApiWrapper
     */
    public function setUnsplashAction(Request $request)
    {
        $image    = json_decode($request->getContent());
        $themeSet = $this->getEditThemeSet();
        $themeSet->setOption('unsplash_image', [
            'url'   => $image->urls->raw,
            'thumb' => $image->urls->thumb,
        ]);
        // Trigger Download on unsplash api to register photo usage
        $client = new Client();
        $client->request('GET', $image->links->download);

        $this->getManager()->persist($themeSet);
        $this->getManager()->flush();

        return $this->wrap([
            'url' => $image->urls->thumb,
        ]);
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
     * @Route("/portal/api/style/edit-theme-set/splash_image")
     * @Method({"GET"})
     *
     * @return ApiWrapper
     */
    public function getCustomSplashImageUrlAction()
    {
        $themeSet = $this->getEditThemeSet();
        $image    = $themeSet->getOption('unsplash_image');
        if ($image) {
            return $this->wrap(['url' => $image['thumb']]);
        }

        return $this->wrap($this->getAssetsManager()->getEditThemeSetBlobAsset(AssetsManager::CUSTOM_SPLASH_IMAGE_TAG));
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

    /**
     * @Route("/portal/api/style/edit-theme-set/splash_image")
     * @Method({"DELETE"})
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return ApiWrapper
     */
    public function deleteEditThemeSetSplashImageAssetAction()
    {
        $themeSet = $this->getEditThemeSet();
        $themeSet->setOption('unsplash_image', null);
        $this->getManager()->persist($themeSet);
        $this->getManager()->flush();

        return $this->wrap($this->getAssetsManager()->deleteEditThemeSetAsset(
            $this->getAssetsManager()->getEditThemeSetBlobAsset(AssetsManager::CUSTOM_SPLASH_IMAGE_TAG)
        ));
    }
}

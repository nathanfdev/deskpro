<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Controller\Api\PortalDesigner;

use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\PortalBundle\Controller\Api\AbstractApiController;
use DeskPRO\Bundle\PortalBundle\Designer\AssetsManager;
use DeskPRO\Bundle\PortalBundle\Form\Form\Type\ImageType;
use Exception;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class LogoController.
 */
class BrandController extends AbstractApiController
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
        $form = $this->createForm(ImageType::class);
        $form->submit($request->files);
        if (!$form->isValid()) {
            return $this->generateFormErrorsResponse($form);
        }

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
     * @return ApiWrapper|View
     */
    public function uploadFaviconAction(Request $request)
    {
        $form = $this->createForm(ImageType::class);
        $form->submit($request->files);
        if (!$form->isValid()) {
            return $this->generateFormErrorsResponse($form);
        }

        try {
            return $this->wrap(
                $this->getAssetsManager()->uploadBlob($request->files->get('file'), AssetsManager::CUSTOM_FAVICON_TAG)
            );
        } catch (Exception $e) {
            $errors = ['fields' => ['file' => ['errors' => [['code' => 'wrong_type', 'message' => 'This image could not
             be 
            processed']]]]];

            return new View($errors, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/logo")
     * @Method({"GET"})
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
        $assetsManager = $this->getAssetsManager();
        $assetsManager->deleteEditThemeSetAsset($assetsManager->getEditThemeSetBlobAsset(AssetsManager::CUSTOM_LOGO_TAG));

        return $this->wrap(null);
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/favicon")
     * @Method({"DELETE"})
     *
     * @return ApiWrapper
     */
    public function deleteEditThemeSetFaviconAssetAction()
    {
        $assetsManager = $this->getAssetsManager();
        $assetsManager->deleteEditThemeSetAsset($assetsManager->getEditThemeSetBlobAsset(AssetsManager::CUSTOM_FAVICON_TAG));
        $assetsManager->deleteEditThemeSetAsset($assetsManager->getEditThemeSetBlobAsset(AssetsManager::CUSTOM_FAVICON_FALLBACK));

        return $this->wrap(null);
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/welcome-message")
     * @Method({"GET"})
     *
     * @return ApiWrapper
     */
    public function getWelcomeMessageAction()
    {
        $themeSet = $this->getEditThemeSet();

        return $this->wrap($themeSet->getOption('welcome_box'));
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/welcome-message")
     * @Method({"PUT"})
     *
     * @param Request $request
     *
     * @return ApiWrapper
     */
    public function updateWelcomeMessageAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $title    = @$data['title'] ?: '';
        $message  = @$data['message'] ?: '';
        $themeSet = $this->getEditThemeSet();

        if ($title || $message) {
            $themeSet->setOption('welcome_box', [
                'title'   => $title,
                'message' => $message,
            ]);
        } else {
            $themeSet->setOption('welcome_box', null);
        }

        $this->getManager()->persist($themeSet);
        $this->getManager()->flush();

        return $this->wrap([
            'title'   => $title,
            'message' => $message,
        ]);
    }
}

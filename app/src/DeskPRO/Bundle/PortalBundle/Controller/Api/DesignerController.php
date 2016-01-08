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
namespace DeskPRO\Bundle\PortalBundle\Controller\Api;

use Application\DeskPRO\Entity\Blob;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSetAsset;
use DeskPRO\Bundle\PortalBundle\Designer\AdvancedEditsManager;
use DeskPRO\Bundle\PortalBundle\Designer\AssetsManager;
use DeskPRO\Bundle\PortalBundle\Designer\PortalStylesCompiler;
use DeskPRO\Bundle\PortalBundle\Designer\SassDocParser;
use DeskPRO\Bundle\PortalBundle\Designer\StylesManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DesignerController.
 */
class DesignerController extends AbstractApiController
{
    /**
     * @Route("/portal/api/style/variable-groups")
     * @Method({"GET"})
     */
    public function getVariableGroupsAction()
    {
        return new JsonResponse($this->getSassDocParser()->getVariableGroups());
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/variable-values")
     * @Method({"GET"})
     */
    public function getVariableValuesAction()
    {
        return new JsonResponse($this->getStylesManager()->getEditThemeSetVariableValues());
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/variable-values")
     * @Method({"PUT"})
     */
    public function saveVariableValuesAction(Request $request)
    {
        $variables = json_decode($request->getContent(), true);
        $this->getPortalStylesCompiler()->recompile($variables);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/advanced-edits")
     * @Method({"GET"})
     */
    public function getAdvancedEditsAction()
    {
        return new JsonResponse($this->getAdvancedEditsManager()->get());
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/advanced-edits")
     * @Method({"PUT"})
     */
    public function saveAdvancedEditsAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $this->getAdvancedEditsManager()->save($data);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/commit")
     * @Method({"GET"})
     */
    public function commitEditThemeSetAction()
    {
        return new JsonResponse($this->getStylesManager()->commitEditThemeSet());
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/discard")
     * @Method({"GET"})
     */
    public function discardEditThemeSetAction()
    {
        return new JsonResponse($this->getStylesManager()->discardEditThemeSet());
    }

    /**
     * @Route("/portal/api/style/portal.css", name="dp_portal_designer_custom_css")
     * @Method({"GET"})
     */
    public function getCssFileAction(Request $request)
    {
        $blob_storage = $request->get('preview')
                      ? $this->getStylesManager()->getEditThemeSetCssBlobStorage()
                      : $this->getStylesManager()->getCssBlobStorage();

        if (!$blob_storage) {
            throw $this->createNotFoundException('Custom styles not found');
        }

        return new Response($blob_storage->data, 200, ['Content-Type' => 'text/css']);
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/assets")
     * @Method({"GET"})
     */
    public function listEditThemeSetAssetsAction()
    {
        return $this->dataSerialize($this->getAssetsManager()->getEditThemeSetAssets());
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/assets")
     * @Method({"POST"})
     */
    public function uploadEditThemeSetAssetAction(Request $request)
    {
        return $this->dataSerialize($this->getAssetsManager()->uploadEditThemeSetAsset($request->files->get('file')));
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/assets/{id}", requirements={"id"="\d+"})
     * @Method({"DELETE"})
     * @ParamConverter("asset", class="App:ThemeSetAsset")
     */
    public function deleteEditThemeSetAssetAction(ThemeSetAsset $asset)
    {
        return $this->dataSerialize($this->getAssetsManager()->deleteEditThemeSetAsset($asset));
    }

    /**
     * @Route("/portal/api/style/assets/{name}", name="dp_portal_custom_asset")
     * @Method({"GET"})
     */
    public function serveAssetAction($name)
    {
        if (!$blob_storage = $this->getAssetsManager()->getAssetBlobStorage($name)) {
            throw $this->createNotFoundException('Asset file not found');
        }
        if (!$blob = $this->getManager()->find(Blob::class, $blob_storage->getBlobId())) {
            throw $this->createNotFoundException('Asset file info not found');
        }

        return new Response($blob_storage->data, 200, ['Content-Type' => $blob->content_type]);
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/logo")
     * @Method({"POST"})
     */
    public function uploadLogoAction(Request $request)
    {
        return $this->dataSerialize($this->getAssetsManager()->uploadLogo($request->files->get('file')));
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/logo")
     * @Method({"GET"})
     */
    public function getCustomLogoUrlAction()
    {
        return $this->dataSerialize($this->getAssetsManager()->getEditThemeSetLogoAsset());
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/logo")
     * @Method({"DELETE"})
     */
    public function deleteEditThemeSetLogoAssetAction()
    {
        return $this->dataSerialize($this->getAssetsManager()->deleteEditThemeSetAsset(
            $this->getAssetsManager()->getEditThemeSetLogoAsset()
        ));
    }

    /**
     * @return StylesManager
     */
    private function getStylesManager()
    {
        return $this->get('dp.portal.designer.styles_manager');
    }

    /**
     * @return AdvancedEditsManager
     */
    private function getAdvancedEditsManager()
    {
        return $this->get('dp.portal.designer.advanced_edits_manager');
    }

    /**
     * @return AssetsManager
     */
    private function getAssetsManager()
    {
        return $this->get('dp.portal.designer.assets_manager');
    }

    /**
     * @return PortalStylesCompiler
     */
    private function getPortalStylesCompiler()
    {
        return $this->get('dp.portal.designer.portal_styles_compiler');
    }

    /**
     * @return SassDocParser
     */
    private function getSassDocParser()
    {
        return $this->get('dp.portal.designer.sass_doc_parser');
    }
}

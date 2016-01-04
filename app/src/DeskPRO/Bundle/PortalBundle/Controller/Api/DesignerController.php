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

use DeskPRO\Bundle\PortalBundle\Designer\AdvancedEditsManager;
use DeskPRO\Bundle\PortalBundle\Designer\PortalStylesCompiler;
use DeskPRO\Bundle\PortalBundle\Designer\SassDocParser;
use DeskPRO\Bundle\PortalBundle\Designer\StylesManager;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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
     *
     * @return View
     */
    public function getVariableGroupsAction()
    {
        return new JsonResponse($this->getSassDocParser()->getVariableGroups());
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/variable-values")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getVariableValuesAction()
    {
        return new JsonResponse($this->getStylesManager()->getEditThemeSetVariableValues());
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/variable-values")
     * @Method({"PUT"})
     *
     * @param Request $request
     *
     * @return View
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
     *
     * @return View
     */
    public function getAdvancedEditsAction()
    {
        return new JsonResponse($this->getAdvancedEditsManager()->get());
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/advanced-edits")
     * @Method({"PUT"})
     *
     * @param Request $request
     *
     * @return View
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
     *
     * @return View
     */
    public function commitEditThemeSetAction()
    {
        return new JsonResponse($this->getStylesManager()->commitEditThemeSet());
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/discard")
     * @Method({"GET"})
     *
     * @return View
     */
    public function discardEditThemeSetAction()
    {
        return new JsonResponse($this->getStylesManager()->discardEditThemeSet());
    }

    /**
     * @Route("/portal/api/style/portal.css", name="dp_portal_designer_custom_css")
     * @Method({"GET"})
     *
     * @return View
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

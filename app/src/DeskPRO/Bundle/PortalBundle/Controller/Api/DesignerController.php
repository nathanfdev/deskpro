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
        return new JsonResponse($this->getStylesManager()->getVariableGroups());
    }

    /**
     * @Route("/portal/api/style/variable-values")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getVariableValuesAction()
    {
        return new JsonResponse($this->getStylesManager()->getVariableValues());
    }

    /**
     * @Route("/portal/api/style/variable-values")
     * @Method({"PUT"})
     *
     * @param Request $request
     *
     * @return View
     */
    public function saveStyleVariablesAction(Request $request)
    {
        $variables = json_decode($request->getContent(), true);
        $this->getStylesManager()->recompile($variables);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/portal/api/style/portal.css", name="dp_portal_designer_custom_css")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getCssFileAction()
    {
        if (!$blob_storage = $this->getStylesManager()->getCssBlobStorage()) {
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
}

<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\PortalBundle\Controller\Api\PortalDesigner;

use DeskPRO\Bundle\PortalBundle\Controller\Api\AbstractApiController;
use DeskPRO\Bundle\PortalBundle\Designer\PortalStylesCompiler;
use FOS\RestBundle\Controller\Annotations as Rest;
use Leafo\ScssPhp\Exception\ParserException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class ScssVariablesController.
 *
 * @Rest\Route("/portal/api/style")
 */
class ScssVariablesController extends AbstractApiController
{
    use HelperMethods;

    /**
     * @Rest\Get("/variable-groups")
     */
    public function getVariableGroupsAction()
    {
        return new JsonResponse($this->getSassDocParser()->getVariableGroups());
    }

    /**
     * @Rest\Get("/edit-theme-set/variable-values")
     */
    public function getVariableValuesAction()
    {
        return new JsonResponse($this->getStylesManager()->getEditThemeSetVariableValues());
    }

    /**
     * @Rest\Put("/edit-theme-set/variable-values")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function saveVariableValuesAction(Request $request)
    {
        $editThemeSet = $this->getBrandThemeManager()->getCurrentEditThemeSet();
        $variables    = json_decode($request->getContent(), true);
        if (!is_array($variables)) {
            $variables = [];
        }

        try {
            if ($this->getPortalStylesCompiler()->hasChangedVars($variables, $editThemeSet)) {
                $this->getPortalStylesCompiler()->recompile($variables, $editThemeSet);
            }
        } catch (ParserException $e) {
            throw new BadRequestHttpException(PortalStylesCompiler::parseExceptionMessage($e));
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}

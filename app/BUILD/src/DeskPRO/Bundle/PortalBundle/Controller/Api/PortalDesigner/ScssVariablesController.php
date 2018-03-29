<?php

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

        $this->getManager()->beginTransaction();

        try {
            if ($this->getPortalStylesCompiler()->hasChangedVars($variables, $editThemeSet)) {
                $this->getPortalStylesCompiler()->recompile($variables, $editThemeSet);
            }

            $this->getManager()->commit();
        } catch (ParserException $e) {
            $this->getManager()->rollback();
            throw new BadRequestHttpException(PortalStylesCompiler::parseExceptionMessage($e));
        } catch (\Exception $e) {
            $this->getManager()->rollback();
            throw new BadRequestHttpException($e->getMessage());
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}

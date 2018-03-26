<?php

namespace DeskPRO\Bundle\PortalBundle\Controller\Api\PortalDesigner;

use DeskPRO\Bundle\PortalBundle\Controller\Api\AbstractApiController;
use DeskPRO\Bundle\PortalBundle\Designer\PortalStylesCompiler;
use DpSys\LowError\SystemErrorHandler;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Leafo\ScssPhp\Exception\CompilerException;
use Leafo\ScssPhp\Exception\ParserException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class AdvancedEditsController.
 *
 * @Rest\Route("/portal/api/style/edit-theme-set/advanced-edits")
 */
class AdvancedEditsController extends AbstractApiController
{
    use HelperMethods;

    /**
     * @Rest\Get("")
     *
     * @return View
     */
    public function getAdvancedEditsAction()
    {
        return new View($this->getAdvancedEditsManager()->get());
    }

    /**
     * @Rest\Put("")
     *
     * @param Request $request
     *
     * @return View
     */
    public function saveAdvancedEditsAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $variables    = $this->getStylesManager()->getEditThemeSetVariableValues();
        $editThemeSet = $this->getBrandThemeManager()->getCurrentEditThemeSet();

        $this->getManager()->beginTransaction();

        try {
            $this->getAdvancedEditsManager()->save($data);

            if (
                $this->getAdvancedEditsManager()->hasChangedCssFiles()
                || $this->getPortalStylesCompiler()->hasChangedVars($variables, $editThemeSet)
            ) {
                $this->getPortalStylesCompiler()->recompile($variables, $editThemeSet);
            }

            $this->getManager()->commit();
        } catch (ParserException $e) {
            $this->getManager()->rollback();
            throw new BadRequestHttpException(PortalStylesCompiler::parseExceptionMessage($e));
        } catch (CompilerException $e) {
            $this->getManager()->rollback();
            throw new BadRequestHttpException($e->getMessage());
        } catch (\Exception $e) {
            $this->getManager()->rollback();
            SystemErrorHandler::logException($e, false, null, true);

            throw new BadRequestHttpException($e->getMessage());
        }

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Delete("")
     *
     * @param Request $request
     *
     * @return View
     */
    public function deleteAdvancedEditsAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $this->getManager()->beginTransaction();

        try {
            $this->getAdvancedEditsManager()->delete($data);
            $this->getManager()->commit();
        } catch (ParserException $e) {
            $this->getManager()->rollback();
            throw new BadRequestHttpException(PortalStylesCompiler::parseExceptionMessage($e));
        }

        return new View(null, Response::HTTP_NO_CONTENT);
    }
}

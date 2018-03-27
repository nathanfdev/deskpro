<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Controller\Api\PortalDesigner;

use Application\DeskPRO\Entity\Template;
use DeskPRO\Bundle\PortalBundle\Controller\Api\AbstractApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class TemplatesController.
 */
class TemplatesController extends AbstractApiController
{
    use HelperMethods;

    /**
     * @Route("/portal/api/style/edit-theme-set/templates")
     * @Method({"GET"})
     */
    public function getTemplatesListAction()
    {
        $templates = [];
        foreach (array_keys($this->getTheme()->getTemplateMap()) as $templateName) {
            $templates[] = [
                'name'      => $templateName,
                'is_custom' => $this->getEditThemeSetTemplate($templateName) ? true : false,
            ];
        }

        return new JsonResponse($templates);
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/template-info")
     * @Method({"GET"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getTemplateSourceAction(Request $request)
    {
        $template_name = $request->get('template');
        if ($template = $this->getEditThemeSetTemplate($template_name)) {
            $source   = $template->getTemplateCode();
            $isCustom = true;
        } else {
            $theme    = $this->getTheme();
            $source   = file_get_contents($this->getThemeResolver()->templatePath($theme, $template_name));
            $isCustom = false;
        }

        return new JsonResponse([
            'source'    => $source,
            'is_custom' => $isCustom,
        ]);
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/template-sources")
     * @Method({"PUT"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateTemplateSourceAction(Request $request)
    {
        $template_name = $request->get('template');
        if (!$template = $this->getEditThemeSetTemplate($template_name)) {
            $template            = new Template();
            $template->theme_set = $this->getEditThemeSet();
            $template->name      = $template_name;
        }

        $data = json_decode($request->getContent(), true);
        if (!array_key_exists('code', $data) && !array_key_exists('revert', $data)) {
            throw new BadRequestHttpException('Request body must contain "code" or "revert" props');
        }

        try {
            if (!empty($data['revert'])) {
                $this->getManager()->remove($template);
            } else {
                $template->setTemplate(
                    $data['code'],
                    $this->get('twig')->compileSource($template->template_code, $template_name)
                );
                $this->getManager()->persist($template);
            }
        } catch (\Twig_Error $e) {
            throw new BadRequestHttpException('Template compilation error: '.$e->getMessage());
        }

        $this->getManager()->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}

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
        return new JsonResponse(array_keys($this->getTheme()->getTemplateMap()));
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/template-sources")
     * @Method({"GET"})
     */
    public function getTemplateSourceAction(Request $request)
    {
        $template_name = $request->get('template');
        if ($template = $this->getEditThemeSetTemplate($template_name)) {
            $source = $template->getTemplateCode();
        } else {
            $theme  = $this->getTheme();
            $source = file_get_contents($this->getThemeResolver()->templatePath($theme, $template_name));
        }

        return new JsonResponse($source);
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/template-sources")
     * @Method({"PUT"})
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

        if (!empty($data['revert'])) {
            $this->getManager()->remove($template);
        } else {
            $template->template_code     = $data['code'];
            $template->template_compiled = $this->get('twig')->compileSource($template->template_code, $template_name);
            $this->getManager()->persist($template);
        }

        $this->getManager()->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}

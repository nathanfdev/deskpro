<?php

namespace DeskPRO\Bundle\PortalBundle\Controller\Api\PortalDesigner;

use DeskPRO\Bundle\PortalBundle\Controller\Api\AbstractApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class ThemeSetController.
 */
class ThemeSetController extends AbstractApiController
{
    use HelperMethods;

    /**
     * @Route("/portal/api/style/edit-theme-set/info")
     * @Method({"GET"})
     */
    public function getThemeSetAction()
    {
        $themeSet = $this->getEditThemeSet();

        return new JsonResponse([
            'id'       => $themeSet->getId(),
            'theme_id' => $themeSet->getThemeId(),
        ]);
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/info")
     * @Method({"PUT"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function setThemeSetAction(Request $request)
    {
        $themeSet = $this->getEditThemeSet();

        $data = json_decode($request->getContent(), true);
        if (!array_key_exists('theme_id', $data)) {
            throw new BadRequestHttpException('Request body must contain "theme_id" property');
        }

        $themeId = $data['theme_id'];
        if ($themeId !== 'standard' && $themeId !== 'sidebar') {
            throw new BadRequestHttpException('Invalid theme_id option');
        }

        $themeSet->setThemeId($themeId);
        $this->getManager()->persist($themeSet);
        $this->getManager()->flush();

        return new JsonResponse([
            'id'       => $themeSet->getId(),
            'theme_id' => $themeSet->getThemeId(),
        ]);
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/commit")
     * @Method({"GET"})
     */
    public function commitEditThemeSetAction()
    {
        $this->getStylesManager()->commitEditThemeSet();

        return new JsonResponse();
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/discard")
     * @Method({"GET"})
     */
    public function discardEditThemeSetAction()
    {
        $this->getStylesManager()->discardEditThemeSet();

        return new JsonResponse();
    }
}

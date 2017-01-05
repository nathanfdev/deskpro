<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

use DeskPRO\Bundle\AppBundle\Entity\ThemeSetAsset;
use DeskPRO\Bundle\PortalBundle\Controller\Api\AbstractApiController;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AssetsController.
 */
class AssetsController extends AbstractApiController
{
    use HelperMethods;

    /**
     * @Route("/portal/api/style/edit-theme-set/assets")
     * @Method({"GET"})
     */
    public function listEditThemeSetAssetsAction()
    {
        return $this->wrap($this->getAssetsManager()->getEditThemeSetAssets());
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/assets")
     * @Method({"POST"})
     *
     * @param Request $request
     *
     * @return View
     */
    public function uploadEditThemeSetAssetAction(Request $request)
    {
        return $this->wrap($this->getAssetsManager()->uploadEditThemeSetAsset($request->files->get('file')));
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/assets/{id}", requirements={"id"="\d+"})
     * @Method({"DELETE"})
     * @ParamConverter("asset", class="AppBundle:ThemeSetAsset")
     *
     * @param ThemeSetAsset $asset
     *
     * @return View
     */
    public function deleteEditThemeSetAssetAction(ThemeSetAsset $asset)
    {
        $this->getAssetsManager()->deleteEditThemeSetAsset($asset);

        return new Response(null, Response::HTTP_OK);
    }
}

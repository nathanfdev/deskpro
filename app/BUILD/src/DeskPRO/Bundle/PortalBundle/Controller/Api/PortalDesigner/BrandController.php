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

use DeskPRO\Bundle\PortalBundle\Controller\Api\AbstractApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class LogoController.
 */
class BrandController extends AbstractApiController
{
    use HelperMethods;

    /**
     * @Route("/portal/api/style/edit-theme-set/logo")
     * @Method({"POST"})
     */
    public function uploadLogoAction(Request $request)
    {
        return $this->wrap($this->getAssetsManager()->uploadLogo($request->files->get('file')));
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/logo")
     * @Method({"GET"})
     */
    public function getCustomLogoUrlAction()
    {
        return $this->wrap($this->getAssetsManager()->getEditThemeSetLogoAsset());
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/logo")
     * @Method({"DELETE"})
     */
    public function deleteEditThemeSetLogoAssetAction()
    {
        return $this->wrap($this->getAssetsManager()->deleteEditThemeSetAsset(
            $this->getAssetsManager()->getEditThemeSetLogoAsset()
        ));
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/welcome-message")
     * @Method({"GET"})
     */
    public function getWelcomeMessageAction()
    {
        $themeSet = $this->getEditThemeSet();

        return $this->wrap($themeSet->getOption('welcome_box'));
    }

    /**
     * @Route("/portal/api/style/edit-theme-set/welcome-message")
     * @Method({"PUT"})
     */
    public function updateWelcomeMessageAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $title    = @$data['title']   ?: '';
        $message  = @$data['message'] ?: '';
        $themeSet = $this->getEditThemeSet();

        if ($title || $message) {
            $themeSet->setOption('welcome_box', [
                'title'   => $title,
                'message' => $message,
            ]);
        } else {
            $themeSet->setOption('welcome_box', null);
        }

        $this->getManager()->persist($themeSet);
        $this->getManager()->flush();

        return $this->wrap([
            'title'   => $title,
            'message' => $message,
        ]);
    }
}

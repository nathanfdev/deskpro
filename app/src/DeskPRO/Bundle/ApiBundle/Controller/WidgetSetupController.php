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
namespace DeskPRO\Bundle\ApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class WidgetSetupController.
 */
class WidgetSetupController extends BaseController
{
    /**
     * @Get("/widget/setup", name="api_widget_setup_get")
     *
     * @return View
     */
    public function getWidgetConfigurationAction()
    {
        $asset_package = $this->container->get('templating.asset.package.app_assets.http');
        $base_router   = $this->container->get('router');

        $settings_resolver = $this->container->get('settings_resolver');
        $settings          = $settings_resolver->getGlobalSettings();

        return new View([
            'url' => [
                'widget_loader' => $asset_package->getUrl('widget_loader.js'),
                'widget_bundle' => $asset_package->getUrl('DeskPRO_WidgetBundle.js'),
                'helpdesk'      => $base_router->generate('portal_home', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ],
            'company' => [
                'name' => $settings->get('core.site_name'),
                'logo' => '',
            ],
        ]);
    }

    /**
     * @Post("/widget/setup", name="api_widget_setup_post")
     *
     * @param Request $request
     *
     * @return View
     */
    public function postWidgetConfigurationAction(Request $request)
    {
        return new View();
    }
}

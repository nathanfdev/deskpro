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

namespace DeskPRO\Bundle\ApiBundle\Controller\Settings;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\Settings\PortalGeneralSettingsType;
use DeskPRO\Bundle\AppBundle\Settings\Model\PortalGeneralSettings;
use DeskPRO\Bundle\AppBundle\Settings\PortalSettingsResolver;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class BrandSettingsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/settings/brands/{brandId}")
 */
class BrandSettingsController extends AbstractBrandAwareSettingsController
{
    /**
     * @return PortalGeneralSettings
     */
    protected function getModel()
    {
        /** @var PortalGeneralSettings $settings */
        $settings = $this->get('portal_settings_resolver')->getGeneralSettings();

        $settings->setBrand($this->brand);

        return $settings;
    }

    protected function getType()
    {
        return PortalGeneralSettingsType::class;
    }

    /**
     * @param PortalGeneralSettings $model
     * @param int                   $brandId
     */
    protected function persistModel($model, $brandId)
    {
        $brand = $this->getBrand($brandId);

        $settings_repository = $this->getSettingRepository();
        $settings_repository
            ->updateSetting(PortalSettingsResolver::SITE_NAME, $model->getSiteName(), $brand)
            ->updateSetting(PortalSettingsResolver::SITE_URL, $model->getSiteUrl(), $brand)
            ->updateSetting(PortalSettingsResolver::APPS_FEEDBACK, $model->isAppsFeedback(), $brand)
            ->updateSetting(PortalSettingsResolver::APPS_KB, $model->isAppsKb(), $brand)
            ->updateSetting(PortalSettingsResolver::APPS_NEWS, $model->isAppsNews(), $brand)
            ->updateSetting(PortalSettingsResolver::APPS_DOWNLOADS, $model->isAppsDownloads(), $brand)
            ->updateSetting(PortalSettingsResolver::IFACE_PORTAL, $model->isIfacePortal(), $brand)
            ->updateSetting(PortalSettingsResolver::IFACE_WIDGET, $model->isIfaceWidget(), $brand)
            ->updateSetting(PortalSettingsResolver::SHOW_RATINGS, $model->isShowRatings(), $brand)
            ->updateSetting(PortalSettingsResolver::SHOW_RATINGS_MIN_VOTES, $model->getShowRatingsMinVotes(), $brand)
            ->updateSetting(PortalSettingsResolver::PUBLISH_COMMENTS, $model->isPublishComments(), $brand)
        ;
    }

    /**
     * @ApiDoc(
     *     section="Brand Settings",
     *     description="Get portal general settings",
     *     statusCodes={
     *         200="Success",
     *         404="Not Found error will returned in case we can't find the specified brand"
     *     },
     *     output={
     *          "class"="Application\DeskPRO\Settings\GeneralPortalSettings"
     *      }
     * )
     *
     * @Rest\Get("/portal_general")
     *
     * @param int $brandId
     *
     * @return View
     */
    public function getPortalGeneralAction($brandId)
    {
        $this->setBrandStack($brandId);

        return new View($this->wrap($this->getModel()));
    }

    /**
     * Save general settings.
     *
     * @ApiDoc(
     *     section="Brand Settings",
     *     description="Save portal general settings",
     *
     *     statusCodes={
     *         200="Returned if request was successful",
     *         400="In case your request was malformed",
     *     },
     *     input= {
     *         "class"="DeskPRO\Bundle\AppBundle\Form\Type\Settings\Widget\PortalGeneralSettingsType",
     *         "name"="",
     *         "options"={"method"="POST"},
     *     }
     *)
     * @Rest\Post("/portal_general")
     *
     * @param Request $request
     * @param int     $brandId
     *
     * @return View
     */
    public function postPortalGeneralAction(Request $request, $brandId)
    {
        $this->handleForm($request, $brandId);

        return new View(null, Response::HTTP_NO_CONTENT);
    }
}

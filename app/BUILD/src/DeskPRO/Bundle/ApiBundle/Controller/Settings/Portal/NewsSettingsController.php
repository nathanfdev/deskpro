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

namespace DeskPRO\Bundle\ApiBundle\Controller\Settings\Portal;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\Settings\AbstractBrandAwareSettingsController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\Settings\Portal\NewsSettingsType;
use DeskPRO\Bundle\AppBundle\Settings\Model\Portal\NewsSettings;
use DeskPRO\Bundle\AppBundle\Settings\PortalSettingsResolver;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class NewsSettingsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/settings/brands/{brandId}/portal/news")
 */
class NewsSettingsController extends AbstractBrandAwareSettingsController
{
    /**
     * @var NewsSettings
     */
    protected $model;

    /**
     * @return NewsSettings
     */
    protected function getModel()
    {
        $this->model = $this->get('portal_settings_resolver')->getNewsSettings();

        $this->model->setBrand($this->brand);

        return $this->model;
    }

    protected function getType()
    {
        return NewsSettingsType::class;
    }

    /**
     * @param NewsSettings $model
     * @param int          $brandId
     */
    protected function persistModel($model, $brandId)
    {
        $brand = $this->getBrand($brandId);

        $settings_repository = $this->getSettingRepository();
        $settings_repository
            ->updateSetting(PortalSettingsResolver::APPS_NEWS, $model->isEnabled(), $brand)
            ->updateSetting(PortalSettingsResolver::TAB_NEWS, $model->isTabEnabled(), $brand)
            ->updateSetting(PortalSettingsResolver::SUBSCRIPTION_NEWS, $model->isSubscriptions(), $brand)
        ;
    }

    /**
     * @ApiDoc(
     *     section="Portal Settings",
     *     description="Get portal general settings",
     *     statusCodes={
     *         200="Success",
     *         404="Not Found error will returned in case we can't find the specified brand"
     *     },
     *     output={
     *          "class"="DeskPRO\Bundle\AppBundle\Settings\Model\Portal\NewsSettings"
     *      }
     * )
     *
     * @Rest\Get("")
     *
     * @param int $brandId
     *
     * @return View
     */
    public function getAction($brandId)
    {
        $this->setBrandStack($brandId);

        return new View($this->wrap($this->getModel()));
    }

    /**
     * Save general settings.
     *
     * @ApiDoc(
     *     section="Portal Settings",
     *     description="Save portal general settings",
     *
     *     statusCodes={
     *         200="Returned if request was successful",
     *         400="In case your request was malformed",
     *     },
     *     input= {
     *         "class"="DeskPRO\Bundle\AppBundle\Form\Type\Settings\Widget\Portal\NewsSettingsType",
     *         "name"="",
     *         "options"={"method"="POST"},
     *     }
     *)
     * @Rest\Post("")
     *
     * @param Request $request
     * @param int     $brandId
     *
     * @return View
     */
    public function postAction(Request $request, $brandId)
    {
        $this->handleForm($request, $brandId);

        return new View(null, Response::HTTP_NO_CONTENT);
    }
}

<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Settings\Portal;

use Application\DeskPRO\Entity\Brand;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\Settings\AbstractBrandAwareSettingsController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\Settings\Portal\FeedbackSettingsType;
use DeskPRO\Bundle\AppBundle\Settings\Model\AbstractBrandAwareSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\Portal\FeedbackSettings;
use DeskPRO\Bundle\AppBundle\Settings\PortalSettingsResolver;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FeedbackSettingsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/settings/brands/{brand}/portal/feedback")
 */
class FeedbackSettingsController extends AbstractBrandAwareSettingsController
{
    /**
     * @ApiDoc(
     *     section="Portal Settings",
     *     description="Get portal general settings",
     *     statusCodes={
     *         200="Success",
     *         404="Not Found error will returned in case we can't find the specified brand"
     *     },
     *     output="DeskPRO\Bundle\AppBundle\Settings\Model\Portal\FeedbackSettings"
     * )
     *
     * @Rest\Get("")
     *
     * @param Brand $brand
     *
     * @return View
     */
    public function getAction(Brand $brand)
    {
        return new View($this->wrap($this->getModel($brand)));
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
     *         "class"="DeskPRO\Bundle\AppBundle\Form\Type\Settings\Portal\FeedbackSettingsType"
     *     },
     *     noOutput=true
     *)
     * @Rest\Post("")
     *
     * @param Request $request
     * @param Brand   $brand
     *
     * @return View
     */
    public function postAction(Request $request, Brand $brand)
    {
        $this->handleForm($request, $this->getModel($brand));

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * {@inheritdoc}
     *
     * @return FeedbackSettings
     */
    protected function getModel(Brand $brand)
    {
        return $this->get('portal_settings_resolver')->getFeedbackSettings($brand);
    }

    /**
     * {@inheritdoc}
     */
    protected function getType()
    {
        return FeedbackSettingsType::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param FeedbackSettings $model
     */
    protected function persistModel(AbstractBrandAwareSettings $model)
    {
        $brand = $model->getBrand();
        $this
            ->getSettingRepository()
            ->updateSetting(PortalSettingsResolver::APPS_FEEDBACK, $model->isEnabled(), $brand)
            ->updateSetting(PortalSettingsResolver::TAB_FEEDBACK, $model->isTabEnabled(), $brand)
            ->updateSetting(PortalSettingsResolver::SUBSCRIPTION_FEEDBACK, $model->isSubscriptions(), $brand)
        ;
    }
}

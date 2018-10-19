<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Settings\Portal;

use Application\DeskPRO\Entity\Brand;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\Settings\AbstractBrandAwareSettingsController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\Settings\Portal\GeneralSettingsType;
use DeskPRO\Bundle\AppBundle\Helper\UrlHostChecker;
use DeskPRO\Bundle\AppBundle\Settings\Model\AbstractBrandAwareSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\Portal\GeneralSettings;
use DeskPRO\Bundle\AppBundle\Settings\PortalSettingsResolver;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class BrandSettingsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/settings/brands")
 */
class GeneralSettingsController extends AbstractBrandAwareSettingsController
{
    /**
     * @ApiDoc(
     *     section="Portal New Settings",
     *     description="Get portal general settings",
     *     statusCodes={
     *         200="Success",
     *         404="Not Found error will returned in case we can't find the specified brand"
     *     },
     *     output="Application\DeskPRO\Settings\GeneralPortalSettings"
     * )
     *
     * @Rest\Get("/new/portal/general")
     *
     * @return View
     */
    public function getNewAction()
    {
        $defaultId = $this->get('settings_resolver')->getGlobalSettings()->get('portal.default_brand');
        $brand     = $this->getManager()->getRepository(Brand::class)->find($defaultId);

        return new View($this->wrap($this->getModel($brand)));
    }

    /**
     * @ApiDoc(
     *     section="Portal Settings",
     *     description="Get portal general settings",
     *     statusCodes={
     *         200="Success",
     *         404="Not Found error will returned in case we can't find the specified brand"
     *     },
     *     output="Application\DeskPRO\Settings\GeneralPortalSettings"
     * )
     *
     * @Rest\Get("/{brand}/portal/general")
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
     *         204="Returned if request was successful",
     *         400="In case your request was malformed",
     *     },
     *     input= {
     *         "class"="DeskPRO\Bundle\AppBundle\Form\Type\Settings\Portal\GeneralSettingsType"
     *     },
     *     output="Application\DeskPRO\Settings\GeneralPortalSettings"
     *)
     * @Rest\Post("/{brand}/portal/general")
     *
     * @param Request $request
     * @param Brand   $brand
     *
     * @throws \Exception
     *
     * @return View
     */
    public function postAction(Request $request, Brand $brand)
    {
        $model = $this->getModel($brand);

        $form = $this->createForm($this->getType(), $model);
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        /** @var UrlHostChecker $urlHostChecker */
        $urlHostChecker = $this->get('url_host_checker');
        $url            = $urlHostChecker->simplifyUrl($model->getDeskproUrl());

        $em = $this->getManager();
        if ($brand->getId() != $this->get('settings_resolver')->getGlobalSettings()->get('portal.default_brand')) {
            $helpdeskUrl = $this->get('settings_resolver')->getGlobalSettings()->get('core.deskpro_url');
            $helpdeskUrl = $urlHostChecker->simplifyUrl($helpdeskUrl);

            if ($url === $helpdeskUrl) {
                throw new BadRequestHttpException(
                    'Your brand URL must be a completely separate URL, it cannot be a sub-directory of any of your existing brands.'
                );
            }
        }

        $brand->setUrl($url);
        $brand->setSlug($model->getBrandSlug());
        $brand->setName($model->getBrandName());

        $em->persist($brand);
        $em->flush();

        $model->setBrandSlug($brand->getSlug());

        $this->persistModel($model);

        if (defined('DPC_IS_CLOUD')) {
            \Cloud\LegacyApiBundle\Helper\CloudBrandHelper::flushBrandDomains();
        }

        return new View($this->wrap($this->getModel($brand)));
    }

    /**
     * {@inheritdoc}
     *
     * @return GeneralSettings
     */
    protected function getModel(Brand $brand)
    {
        return $this->get('portal_settings_resolver')->getGeneralSettings($brand);
    }

    /**
     * {@inheritdoc}
     */
    protected function getType()
    {
        return GeneralSettingsType::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param GeneralSettings $model
     */
    protected function persistModel(AbstractBrandAwareSettings $model)
    {
        $brand = $model->getBrand();
        $this
            ->getSettingRepository()
            ->updateSetting(PortalSettingsResolver::SITE_NAME, $model->getSiteName(), $brand)
            ->updateSetting(PortalSettingsResolver::SITE_URL, $model->getSiteUrl(), $brand)
            ->updateSetting(PortalSettingsResolver::HELPDESK_NAME, $model->getDeskproName(), $brand)
            ->updateSetting(PortalSettingsResolver::HELPDESK_URL, $model->getDeskproUrl(), $brand)
            ->updateSetting(PortalSettingsResolver::APPS_FEEDBACK, $model->isAppsFeedback(), $brand)
            ->updateSetting(PortalSettingsResolver::APPS_KB, $model->isAppsKb(), $brand)
            ->updateSetting(PortalSettingsResolver::APPS_NEWS, $model->isAppsNews(), $brand)
            ->updateSetting(PortalSettingsResolver::APPS_DOWNLOADS, $model->isAppsDownloads(), $brand)
            ->updateSetting(PortalSettingsResolver::APPS_GUIDES, $model->isAppsDownloads(), $brand)
            ->updateSetting(PortalSettingsResolver::IFACE_PORTAL, $model->isIfacePortal(), $brand)
            ->updateSetting(PortalSettingsResolver::IFACE_WIDGET, $model->isIfaceWidget(), $brand)
            ->updateSetting(PortalSettingsResolver::SHOW_RATINGS, $model->isShowRatings(), $brand)
            ->updateSetting(PortalSettingsResolver::SHOW_RATINGS_MIN_VOTES, $model->getShowRatingsMinVotes(), $brand)
            ->updateSetting(PortalSettingsResolver::PUBLISH_COMMENTS, $model->isPublishComments(), $brand)
        ;
    }
}

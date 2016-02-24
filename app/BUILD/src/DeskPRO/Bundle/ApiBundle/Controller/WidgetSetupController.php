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

use Application\DeskPRO\Entity\DataStore;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Widget\WidgetSettings;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class WidgetSetupController.
 *
 * @ApiModes("all")
 */
class WidgetSetupController extends BaseController
{
    /**
     * @Get("/widget/setup", name="api_widget_setup_get")
     *
     * @return View
     */
    public function getWidgetSetupAction()
    {
        /** @var \Symfony\Component\Asset\Packages $asset_package */
        $asset_package = $this->container->get('assets.packages');
        $base_router   = $this->container->get('router');

        $widget_settings = $this->container->get('widget.settings');
        $brand_settings  = array_merge(
            $widget_settings->getDefaultBrandSettings(),
            $this->getOrCreateWidgetBrandSettings()->getData('brand_settings') ?: []
        );

        return new View([
            'data' => [
                'url' => [
                    'widget_loader' => $asset_package->getUrl('widget_loader.js', 'app_assets'),
                    'widget_bundle' => $asset_package->getUrl('DeskPRO_WidgetBundle.js', 'app_assets'),
                    'helpdesk'      => $base_router->generate('portal_home', [], UrlGeneratorInterface::ABSOLUTE_URL),
                ],
                'company'  => $widget_settings->getCompanySettings(),
                'settings' => [
                    'global' => [
                        'chat' => [
                            'require_login'    => $widget_settings->isPortalRequireLogin(),
                            'email_validation' => $widget_settings->isPortalEmailValidation(),
                        ],
                    ],
                    'brand' => $brand_settings,
                ],
                'enabled_on_portal' => $widget_settings->isEnabledOnPortal(),
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
    public function postWidgetSetupAction(Request $request)
    {
        $this->handleForm($request);

        return new View();
    }

    /**
     * @Post("/widget/portal/apply", name="api_widget_portal_apply")
     *
     * @param Request $request
     *
     * @return View
     */
    public function applyPortalWidgetSettingsAction(Request $request)
    {
        // store form settings
        $this->handleForm($request);

        // update portal widget brand settings as well
        $data_store = $this->getOrCreatePortalWidgetBrandSettings();
        $data_store->setData('brand_settings', $this->getOrCreateWidgetBrandSettings()->getData('brand_settings'));

        $em = $this->getManager();
        $em->persist($data_store);
        $em->flush();

        // enable widget on the portal
        $setting_repo = $this->getSettingsRepository();
        $setting_repo->updateSetting(WidgetSettings::ENABLED_ON_PORTAL, true);

        return new View();
    }

    /**
     * @Post("/widget/portal/remove", name="api_widget_portal_remove")
     *
     * @return View
     */
    public function removePortalWidgetAction()
    {
        $setting_repo = $this->getSettingsRepository();
        $setting_repo->updateSetting(WidgetSettings::ENABLED_ON_PORTAL, false);

        return new View();
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function handleForm(Request $request)
    {
        $form = $this->get('form.factory')->createNamedBuilder(null, 'widget_setup')->getForm();
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        // Save global settings
        $new_global_chat_settings = $form->getData()['global']['chat'];

        $setting_repo = $this->getSettingsRepository();
        $setting_repo->updateSetting(WidgetSettings::EMAIL_VALIDATION, $new_global_chat_settings['email_validation']);
        $setting_repo->updateSetting(WidgetSettings::REQUIRE_LOGIN, $new_global_chat_settings['require_login']);

        $data_store = $this->getOrCreateWidgetBrandSettings();
        $data_store->setData('brand_settings', $form->getData()['brand']);

        $em = $this->getManager();
        $em->persist($data_store);
        $em->flush();
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\Setting
     */
    protected function getSettingsRepository()
    {
        return $this->getRepository('DeskPRO:Setting');
    }

    /**
     * @return DataStore|null
     */
    protected function getOrCreateWidgetBrandSettings()
    {
        return $this->getOrCreateDataStore('widget.brand_settings');
    }

    /**
     * @return DataStore|null
     */
    protected function getOrCreatePortalWidgetBrandSettings()
    {
        return $this->getOrCreateDataStore('widget.portal_brand_settings');
    }

    /**
     * @param string $name
     *
     * @return DataStore
     */
    protected function getOrCreateDataStore($name)
    {
        $data_store = $this->getRepository('DeskPRO:DataStore')->findOneBy(['name' => $name]);
        if (!$data_store) {
            $data_store = new DataStore();
            $data_store->setName($name);
        }

        return $data_store;
    }
}

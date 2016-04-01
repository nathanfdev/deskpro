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

namespace DeskPRO\Bundle\ApiBundle\Controller\Settings;

use Application\DeskPRO\Entity\DataStore;
use Application\DeskPRO\Entity\Setting;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\WidgetSetup\WidgetSetupType;
use DeskPRO\Bundle\AppBundle\Serializer\Model\WidgetSetup;
use DeskPRO\Bundle\AppBundle\Settings\WidgetSettingsResolver;
use DeskPRO\Bundle\AppBundle\Templating\WidgetLoader;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class WidgetSetupController.
 *
 * @ApiModes("all")
 */
class WidgetSetupController extends BaseController
{
    /**
     * Gather widget setup information.
     *
     * @ApiDoc(
     *     section="Widget setup",
     *     resourceDescription="Operations about widget setup",
     *     description="get widget setup",
     *     statusCodes={
     *         200="Returned if request was successful",
     *     },

     *     output="DeskPRO\Bundle\AppBundle\Serializer\Model\WidgetSetup"
     *)
     * @Get("/widget/setup", name="api_widget_setup_get")
     *
     * @return View
     */
    public function getWidgetSetupAction()
    {
        /** @var \Symfony\Component\Asset\Packages $asset_package */
        $asset_package = $this->container->get('assets.packages');
        $base_router   = $this->container->get('router');

        $widget_settings = $this->container->get('widget_settings_resolver');
        $brand_settings  = array_merge(
            $widget_settings->getDefaultBrandSettings(),
            $this->getOrCreateWidgetBrandSettings()->getData('brand_settings') ?: []
        );

        $setup = new WidgetSetup(
            $widget_settings->getCompanySettings(),
            $widget_settings->isEnabledOnPortal(),
            [
                'widget_loader' => $asset_package->getUrl('widget_loader.js', 'app_assets'),
                'widget_bundle' => $asset_package->getUrl('DeskPRO_WidgetBundle.js', 'app_assets'),
                'helpdesk'      => $base_router->generate('portal_home', [], UrlGeneratorInterface::ABSOLUTE_URL),

            ],
            [
                'global' => [
                    'chat' => [
                        'require_login'    => $widget_settings->isPortalRequireLogin(),
                        'email_validation' => $widget_settings->isPortalEmailValidation(),
                    ],
                ],
                'brand' => $brand_settings,
            ]
        );

        return View::create($this->wrap($setup), Response::HTTP_OK);
    }

    /**
     * Get the HTML code for the widget.
     *
     * @ApiDoc(
     *     section="Widget setup",
     *     resourceDescription="Operations about widget setup",
     *     description="Get the HTML code for the widget",
     *     statusCodes={200="Returned if request was successful"},
     *     input={
     *         "name"="settings",
     *         "options"={"method"="POST"},
     *     },
     *     output="DeskPRO\Bundle\AppBundle\Templating\WidgetLaoder"
     * )
     *
     * @Post("/widget/code")
     * @Get("/widget/code")
     *
     * @param Request $request
     *
     * @return View
     */
    public function getWidgetCodeAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            $settings = $request->request->get('settings');
        } else {
            $widget_settings = $this->container->get('widget_settings_resolver');
            $brand_settings  = array_merge(
                $widget_settings->getDefaultBrandSettings(),
                $this->getOrCreateWidgetBrandSettings()->getData('brand_settings') ?: []
            );

            $saved_settings = [
                'global' => [
                    'chat' => [
                        'require_login'    => $widget_settings->isPortalRequireLogin(),
                        'email_validation' => $widget_settings->isPortalEmailValidation(),
                    ],
                ],
                'brand' => $brand_settings,
            ];

            $settings = array_merge($saved_settings['global']['chat'], $saved_settings['brand']);
        }

        $loader = WidgetLoader::createLoader(
            $this->container->get('deskpro.app_env'),
            $settings,
            $this->container->get('brand_stack')->getActive()->getSetting('core.deskpro_url'),
            $this->container->get('assets.packages')
        );

        return View::create($this->wrap($loader), Response::HTTP_OK);
    }

    /**
     * Create widget.
     *
     * @ApiDoc(
     *     section="Widget setup",
     *     resourceDescription="Operations about widget setup",
     *     description="create widget",
     *
     *     statusCodes={
     *         200="Returned if request was successful",
     *         400="In case your request was malformed",
     *     },
     *     input= {
     *         "class"="DeskPRO\Bundle\AppBundle\Form\Type\WidgetSetup\GlobalSettings\WidgetGlobalSetupType",
     *         "name"="",
     *         "options"={"method"="POST"},
     *     }
     *)
     * @Post("/widget/setup", name="api_widget_setup_post")
     *
     * @param Request $request
     *
     * @return View
     */
    public function postWidgetSetupAction(Request $request)
    {
        $this->handleForm($request);

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Apply widget settings.
     *
     * @ApiDoc(
     *     section="Widget setup",
     *     resourceDescription="Operations about widget setup",
     *     description="apply settings",
     *
     *     statusCodes={
     *         200="Returned if request was successful",
     *         400="In case your request was malformed",
     *     },
     *     input= {
     *         "class"="DeskPRO\Bundle\AppBundle\Form\Type\WidgetSetup\GlobalSettings\WidgetGlobalSetupType",
     *         "name"="",
     *         "options"={"method"="POST"},
     *     }
     *)
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
        $setting_repo->updateSetting(WidgetSettingsResolver::ENABLED_ON_PORTAL, true);

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Remove widget.
     *
     * @ApiDoc(
     *     section="Widget setup",
     *     resourceDescription="Operations about widget setup",
     *     description="remove widget",
     *     statusCodes={
     *         200="Returned if request was successful",
     *     },
     *)
     *
     * @Post("/widget/portal/remove", name="api_widget_portal_remove")
     *
     * @return View
     */
    public function removePortalWidgetAction()
    {
        $setting_repo = $this->getSettingsRepository();
        $setting_repo->updateSetting(WidgetSettingsResolver::ENABLED_ON_PORTAL, false);

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function handleForm(Request $request)
    {
        $form = $this->get('form.factory')->createNamedBuilder(null, WidgetSetupType::class)->getForm();
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        // Save global settings
        $new_global_chat_settings = $form->getData()['global']['chat'];

        $setting_repo = $this->getSettingsRepository();
        $setting_repo->updateSetting(WidgetSettingsResolver::EMAIL_VALIDATION, $new_global_chat_settings['email_validation']);
        $setting_repo->updateSetting(WidgetSettingsResolver::REQUIRE_LOGIN, $new_global_chat_settings['require_login']);

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
        return $this->getRepository(Setting::class);
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

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
use DeskPRO\Bundle\AppBundle\Form\Type\Settings\Widget\WidgetSetupType;
use DeskPRO\Bundle\AppBundle\Settings\WidgetSettingsResolver;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class WidgetSettingsController.
 *
 * @ApiModes("all")
 */
class WidgetSettingsController extends BaseController
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
     *
     *     output="DeskPRO\Bundle\AppBundle\Settings\Model\Widget\WidgetSettings"
     *)
     * @Rest\Get("/widget/setup")
     *
     * @return View
     */
    public function getWidgetSetupAction()
    {
        return new View($this->wrap($this->container->get('widget_settings_resolver')->getWidgetSettings()));
    }

    /**
     * Get the HTML code for the widget.
     *
     * @ApiDoc(
     *     section="Widget setup",
     *     resourceDescription="Operations about widget setup",
     *     description="Get the HTML code for the widget",
     *     statusCodes={
     *         200="Returned if request was successful"
     *     },
     *     output="string"
     * )
     *
     * @Rest\Get("/widget/code")
     *
     * @return string
     */
    public function getWidgetCodeAction()
    {
        return new Response($this->get('widget_loader_code_renderer')->getWidgetCode());
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
     *         "class"="DeskPRO\Bundle\AppBundle\Form\Type\Settings\Widget\WidgetSetupType",
     *         "name"="",
     *         "options"={"method"="POST"},
     *     }
     *)
     * @Rest\Post("/widget/setup")
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
     *         "class"="DeskPRO\Bundle\AppBundle\Form\Type\Settings\Widget\WidgetSetupType",
     *         "name"="",
     *         "options"={"method"="POST"},
     *     }
     *)
     * @Rest\Post("/widget/portal/apply")
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
        $dataStore = $this->getOrCreatePortalWidgetBrandSettings();
        $dataStore->setData('brand_settings', $this->getOrCreateWidgetBrandSettings()->getData('brand_settings'));

        $em = $this->getManager();
        $em->persist($dataStore);
        $em->flush();

        // enable widget on the portal
        $settingRepo = $this->getSettingsRepository();
        $settingRepo->updateSetting(WidgetSettingsResolver::ENABLED_ON_PORTAL, true);

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
     * @Rest\Post("/widget/portal/remove")
     *
     * @return View
     */
    public function removePortalWidgetAction()
    {
        $settingRepo = $this->getSettingsRepository();
        $settingRepo->updateSetting(WidgetSettingsResolver::ENABLED_ON_PORTAL, false);

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function handleForm(Request $request)
    {
        $model = $this->container->get('widget_settings_resolver')->getWidgetOptions();

        $form = $this->createForm(WidgetSetupType::class, $model);
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        // Save global settings
        $chatSettings = $model->getGlobal()->getChat();

        $settingRepo = $this->getSettingsRepository();
        $settingRepo->updateSetting(WidgetSettingsResolver::CHAT_EMAIL_VALIDATION, $chatSettings->isEmailValidation());
        $settingRepo->updateSetting(WidgetSettingsResolver::CHAT_REQUIRE_LOGIN, $chatSettings->isRequireLogin());
        $settingRepo->updateSetting(WidgetSettingsResolver::CHAT_ENABLED, $chatSettings->isEnabled());

        // Save brand settings
        $dataStore = $this->getOrCreateWidgetBrandSettings();
        $dataStore->setData('brand_settings', $model->getBrand());

        $em = $this->getManager();
        $em->persist($dataStore);
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
        $dataStore = $this->getRepository(DataStore::class)->findOneBy(['name' => $name]);
        if (!$dataStore) {
            $dataStore = new DataStore();
            $dataStore->setName($name);
        }

        return $dataStore;
    }
}

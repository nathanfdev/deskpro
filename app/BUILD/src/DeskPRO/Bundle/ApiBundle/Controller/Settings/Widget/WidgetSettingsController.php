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

namespace DeskPRO\Bundle\ApiBundle\Controller\Settings\Widget;

use Application\DeskPRO\Entity\DataStore;
use Application\DeskPRO\Entity\Setting;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\Settings\Widget\WidgetOptionsType;
use DeskPRO\Bundle\AppBundle\Settings\WidgetSettingsResolver;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
     * @param Request $request
     *
     * @return string
     */
    public function getWidgetCodeAction(Request $request)
    {
        $withOptions = $request->query->get('options') ? true : false;

        return new Response($this->get('widget_loader_code_renderer')->getWidgetCode($withOptions));
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
        $this->handleForm($request);

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
     *     description="remove widget from portal",
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
     * Reset widget settings.
     *
     * @ApiDoc(
     *     section="Widget setup",
     *     resourceDescription="Operations about widget setup",
     *     description="reset widget settings",
     *     statusCodes={
     *         200="Returned if request was successful",
     *     },
     *)
     *
     * @Rest\Delete("/widget/setup")
     */
    public function resetSettingsAction()
    {
        $settings = $this->getOrCreateWidgetBrandSettings();
        if ($settings->getId()) {
            $this->getManager()->remove($settings);
            $this->getManager()->flush();
        }

        $settingRepo = $this->getSettingsRepository();
        $settingRepo->updateSetting(WidgetSettingsResolver::CHAT_EMAIL_VALIDATION, false);
        $settingRepo->updateSetting(WidgetSettingsResolver::CHAT_REQUIRE_LOGIN, false);
        $settingRepo->updateSetting(WidgetSettingsResolver::CHAT_ENABLED, true);

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @ApiDoc(
     *     section="Widget sample online agents",
     *     resourceDescription="Operations about widget setup",
     *     description="get widget sample online agent",
     *     statusCodes={
     *         204="Returned if request was successful",
     *     },
     *)
     *
     * @Rest\Post("/widget/send-instructions")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function sendInstructionsAction(Request $request)
    {
        $email = $request->request->get('email');
        if (!$email) {
            throw new BadRequestHttpException('You should provide an email!');
        }
        $message = $this->container->get('mailer')->createMessage();
        $message->setTemplate(
            'DeskPRO:emails_common:chat-instructions.html.twig'
        );
        $message->setTo($email);
        $attach = \Swift_Attachment::newInstance(
            $this->get('widget_loader_code_renderer')->getWidgetCode(true),
            'deskpro-widget.txt',
            'text/plain'
        );
        $message->attach($attach);
        $this->container->get('mailer')->send($message);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function handleForm(Request $request)
    {
        $model = $this->container->get('widget_settings_resolver')->getWidgetOptions();

        $form = $this->createForm(WidgetOptionsType::class, $model);
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

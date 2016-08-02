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

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\DataStore;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\Settings\AbstractBrandAwareSettingsController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\Settings\Widget\WidgetOptionsType;
use DeskPRO\Bundle\AppBundle\Settings\Model\AbstractBrandAwareSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\Widget\WidgetSettings;
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
 * @Rest\Route("/settings/brands/{brand}/widget")
 */
class WidgetSettingsController extends AbstractBrandAwareSettingsController
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
     * @Rest\Get("/setup")
     *
     * @param Brand $brand
     *
     * @return View
     */
    public function getWidgetSetupAction(Brand $brand)
    {
        return new View($this->wrap($this->getModel($brand)));
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
     * @Rest\Get("/code")
     *
     * @param Request $request
     * @param Brand   $brand
     *
     * @return string
     */
    public function getWidgetCodeAction(Request $request, Brand $brand)
    {
        $code = $this->get('widget_loader_code_renderer')->getWidgetCode($brand, $request->query->get('options'));

        return new Response($code);
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
     * @Rest\Post("/setup")
     *
     * @param Request $request
     * @param Brand   $brand
     *
     * @return View
     */
    public function postWidgetSetupAction(Request $request, Brand $brand)
    {
        $this->handleForm($request, $this->getModel($brand));

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
     * @Rest\Post("/portal/apply")
     *
     * @param Request $request
     * @param Brand   $brand
     *
     * @return View
     */
    public function applyPortalWidgetSettingsAction(Request $request, Brand $brand)
    {
        $this->handleForm($request, $this->getModel($brand));

        // enable widget on the portal
        $settingRepo = $this->getSettingRepository();
        $settingRepo->updateSetting(WidgetSettingsResolver::ENABLED_ON_PORTAL, true, $brand);

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
     * @Rest\Post("/portal/remove")
     *
     * @param Brand $brand
     *
     * @return View
     */
    public function removePortalWidgetAction(Brand $brand)
    {
        $settingRepo = $this->getSettingRepository();
        $settingRepo->updateSetting(WidgetSettingsResolver::ENABLED_ON_PORTAL, false, $brand);

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
     * @Rest\Delete("/setup")
     * 
     * @param Brand $brand
     *
     * @return View
     */
    public function resetSettingsAction(Brand $brand)
    {
        $settings = $this->getOrCreateWidgetBrandSettings($brand);
        if ($settings->getId()) {
            $this->getManager()->remove($settings);
            $this->getManager()->flush();
        }

        $settingRepo = $this->getSettingRepository();
        $settingRepo->updateSetting(WidgetSettingsResolver::CHAT_EMAIL_VALIDATION, false, $brand);
        $settingRepo->updateSetting(WidgetSettingsResolver::CHAT_REQUIRE_LOGIN, false, $brand);
        $settingRepo->updateSetting(WidgetSettingsResolver::CHAT_ENABLED, true, $brand);

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
     * @Rest\Post("/send-instructions")
     *
     * @param Request $request
     * @param Brand   $brand
     *
     * @return Response
     */
    public function sendInstructionsAction(Request $request, Brand $brand)
    {
        $email = $request->request->get('email');
        if (!$email) {
            throw new BadRequestHttpException('You should provide an email!');
        }
        /** @var \Application\EmailBundle\SwiftMailer\Message\Message $message */
        $message = $this->container->get('mailer')->createMessage();
        $message->setTemplate(
            'DeskPRO:emails_common:chat-instructions.html.twig'
        );
        $message->setTo($email);
        $attach = \Swift_Attachment::newInstance(
            $this->get('widget_loader_code_renderer')->getWidgetCode($brand, true),
            'deskpro-widget.txt',
            'text/plain'
        );
        $message->attach($attach);
        $this->container->get('mailer')->send($message);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * {@inheritdoc}
     *
     * @param WidgetSettings $model
     */
    protected function handleForm(Request $request, AbstractBrandAwareSettings $model)
    {
        $settings = $model->getSettings();

        $form = $this->createForm($this->getType(), $settings);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $this->persistModel($model);

        // Save brand settings
        $dataStore = $this->getOrCreateWidgetBrandSettings($model->getBrand());
        $dataStore->setData('brand_settings', $settings->getBrand());

        $em = $this->getManager();
        $em->persist($dataStore);
        $em->flush();
    }

    /**
     * @param Brand $brand
     *
     * @return DataStore|null
     */
    protected function getOrCreateWidgetBrandSettings(Brand $brand)
    {
        return $this->getOrCreateDataStore('widget.brand_settings.'.$brand->getId());
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

    /**
     * {@inheritdoc}
     *
     * @return WidgetSettings
     */
    protected function getModel(Brand $brand)
    {
        return $this->get('widget_settings_resolver')->getWidgetSettings($brand);
    }

    /**
     * {@inheritdoc}
     */
    protected function getType()
    {
        return WidgetOptionsType::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param WidgetSettings $model
     */
    protected function persistModel(AbstractBrandAwareSettings $model)
    {
        $brand = $model->getBrand();
        $chat  = $model->getSettings()->getGlobal()->getChat();
        $this
            ->getSettingRepository()
            ->updateSetting(WidgetSettingsResolver::CHAT_EMAIL_VALIDATION, $chat->isEmailValidation(), $brand)
            ->updateSetting(WidgetSettingsResolver::CHAT_REQUIRE_LOGIN, $chat->isRequireLogin(), $brand)
            ->updateSetting(WidgetSettingsResolver::CHAT_ENABLED, $chat->isEnabled(), $brand)
        ;
    }
}

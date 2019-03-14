<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Settings\Widget;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\DataStore;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\Settings\AbstractBrandAwareSettingsController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\Settings\Widget\WidgetSettingsType;
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
     * @param Brand   $brand
     * @param Request $request
     *
     * @return string
     */
    public function getWidgetCodeAction(Brand $brand, Request $request)
    {
        $code = $this->get('widget_loader_code_renderer')->getWidgetCode($brand, $request, false, true);

        return new View($this->wrap($code));
    }

    /**
     * Get the HTML code for the widget live demo preview.
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
     * @Rest\Get("/live_demo_code")
     *
     * @param Request $request
     *
     * @return string
     */
    public function getWidgetLiveDemoCodeAction(Request $request)
    {
        $brand = $this->get('brand_stack')->getActive()->getBrand();
        $code  = $this->get('widget_loader_code_renderer')->getWidgetCode($brand, $request, true);

        return new View($this->wrap($code));
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
     *         "class"="DeskPRO\Bundle\AppBundle\Form\Type\Settings\Widget\WidgetSettingsType"
     *     },
     *     output="DeskPRO\Bundle\AppBundle\Settings\Model\Widget\WidgetSettings"
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
     *     parameters={
     *         { "name" = "email", "dataType" = "string", "format" = "string", "required" = true, "description" = "Email address" }
     *     },
     *     noOutput=true
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
            $this->get('widget_loader_code_renderer')->getWidgetCode($brand, $request, false),
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

        $form = $this->createForm($this->getType(), $model);
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
        return WidgetSettingsType::class;
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
            ->updateSetting(WidgetSettingsResolver::CHAT_ENABLED, $chat->isEnabled(), $brand)
            ->updateSetting(WidgetSettingsResolver::ENABLED_ON_PORTAL, $model->isEnabledOnPortal(), $brand)
            ->updateSetting(WidgetSettingsResolver::JWT_SECRET, $model->getJwtSettings()->getSecret(), $brand)
            ->updateSetting(WidgetSettingsResolver::JWT_REQUIRED, $model->getJwtSettings()->isRequired(), $brand)
        ;
    }
}

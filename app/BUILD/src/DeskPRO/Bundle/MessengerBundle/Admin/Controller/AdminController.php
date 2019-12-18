<?php

namespace DeskPRO\Bundle\MessengerBundle\Admin\Controller;

use Application\DeskPRO\Entity\Brand;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\Settings\AbstractBrandAwareSettingsController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Settings\Model\AbstractBrandAwareSettings;
use DeskPRO\Bundle\MessengerBundle\Form\Type\Settings\MessengerType;
use DeskPRO\Bundle\MessengerBundle\Service\MessengerSettingsResolver as MSR;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerSettings;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AdminController.
 *
 * @ApiModes("all")
 * @ApiUserContext("admin")
 * @Rest\Route("/messenger/settings/{brand}")
 * @Feature("messenger")
 */
class AdminController extends AbstractBrandAwareSettingsController
{
    /**
     * Gather widget setup information.
     *
     * @ApiDoc(
     *     section="Messenger setup",
     *     resourceDescription="Operations about messenger setup",
     *     description="get messenger setup",
     *     statusCodes={
     *         200="Returned if request was successful",
     *     },
     *     output="DeskPRO\Bundle\MessengerBundle\Settings\Model\ModelSettings"
     *)
     * @Rest\Get("/setup")
     *
     * @param Brand $brand
     *
     * @return View
     */
    public function getSettingsAction(Brand $brand)
    {
        return View::create($this->wrap($this->getModel($brand)));
    }

    /**
     * Create widget.
     *
     * @ApiDoc(
     *     section="Messenger setup",
     *     resourceDescription="Operations about Messenger setup",
     *     description="create Messenger",
     *
     *     statusCodes={
     *         200="Returned if request was successful",
     *         400="In case your request was malformed",
     *     },
     *     input= {
     *         "class"="DeskPRO\Bundle\MessengerBundle\Form\Type\Settings\MessengerType"
     *     },
     *     output="DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerSettins"
     *)
     * @Rest\Post("/setup")
     *
     * @param Request $request
     * @param Brand   $brand
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     *
     * @return View
     */
    public function postSettingsAction(Request $request, Brand $brand)
    {
        $this->handleForm($request, $this->getModel($brand));

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param Brand $brand
     *
     * @return MessengerSettings
     */
    protected function getModel(Brand $brand)
    {
        return $this->get('messenger.service.settings_resolver')->getMessengerSettings($brand);
    }

    protected function getType()
    {
        return MessengerType::class;
    }

    /**
     * @param Request                    $request
     * @param AbstractBrandAwareSettings $model
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     *
     * @return View|void
     */
    protected function handleForm(Request $request, AbstractBrandAwareSettings $model)
    {
        $form        = $this->createForm($this->getType(), $model, ['brand' => $model->getBrand()]);
        $requestData = $request->request->all();
        if (isset($requestData['messenger']['maxFileSize'])) {
            unset($requestData['messenger']['maxFileSize']);
        }
        $form->submit($requestData);
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $this->persistModel($model);
    }

    /**
     * @param MessengerSettings $model
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    protected function persistModel(AbstractBrandAwareSettings $model)
    {
        $brand                       = $model->getBrand();
        $messengerEmbed              = $model->getEmbed();
        $messengerChat               = $model->getChat();
        $messengerChatTicketDefaults = $messengerChat->getTicketDefaults();
        $messengerChatPreChatForm    = $messengerChat->getPreChatForm();
        $messengerStyles             = $model->getStyles();
        $messengerOptions            = $model->getMessenger();
        $optionsTickets              = $messengerOptions->getTickets();
        $optionsChat                 = $messengerOptions->getChat();
        $messengerTickets            = $model->getTickets();
        $this
            ->getSettingRepository()

            ->updateSetting(MSR::EMBED_AUTHORIZE_DOMAINS, $messengerEmbed->getAuthorizeDomains(), $brand)
            ->updateSetting(MSR::EMBED_ENABLED_ON_PORTAL, $messengerEmbed->isShowOnPortal(), $brand)

            ->updateSetting(MSR::CHAT_TIMEOUT, $messengerChat->getTimeout(), $brand)
            ->updateSetting(MSR::CHAT_PROMPT, $messengerChat->getPrompt(), $brand)
            ->updateSetting(MSR::CHAT_NO_ANSWER_BEHAVIOR, $messengerChat->getNoAnswerBehavior(), $brand)
            ->updateSetting(MSR::CHAT_DEFAULT_DEPARTMENT, $messengerChat->getDepartment(), $brand)
            ->updateSetting(MSR::CHAT_BUSY_MESSAGE, $messengerChat->getBusyMessage(), $brand)
            ->updateSetting(MSR::CHAT_ENABLED, $messengerChat->isEnabled(), $brand)

            ->updateSetting(MSR::PRE_CHAT_FORM_ENABLED, $messengerChatPreChatForm->isEnabled(), $brand)
            ->updateSetting(MSR::PRE_CHAT_FORM_NAME_ENABLED, $messengerChatPreChatForm->isNameEnabled(), $brand)
            ->updateSetting(MSR::PRE_CHAT_FORM_EMAIL_ENABLED, $messengerChatPreChatForm->isEmailEnabled(), $brand)
            ->updateSetting(MSR::PRE_CHAT_FORM_NAME_REQUIRED, $messengerChatPreChatForm->isNameRequired(), $brand)
            ->updateSetting(MSR::PRE_CHAT_FORM_EMAIL_REQUIRED, $messengerChatPreChatForm->isEmailRequired(), $brand)
            ->updateSetting(MSR::PRE_CHAT_FORM_DEPARTMENT, $messengerChatPreChatForm->isDepartmentSelectable(), $brand)
            ->updateSetting(MSR::PRE_CHAT_FORM_FIELDS, serialize($messengerChatPreChatForm->getFields()), $brand)

            ->updateSetting(MSR::CHAT_TICKET_DEFAULTS_SUBJECT, $messengerChatTicketDefaults->getSubject(), $brand)
            ->updateSetting(MSR::CHAT_TICKET_DEFAULTS_DEP, $messengerChatTicketDefaults->getDepartment(), $brand)

            ->updateSetting(MSR::TICKETS_ENABLED, $messengerTickets->isEnabled(), $brand)
            ->updateSetting(MSR::TICKETS_DEPARTMENT, $messengerTickets->getDepartment(), $brand)
            ->updateSetting(MSR::TICKETS_SUBJECT, $messengerTickets->getSubject(), $brand)

            ->updateSetting(MSR::STYLE_PRIMARY_COLOR, $messengerStyles->getPrimaryColor(), $brand)
            ->updateSetting(MSR::STYLE_BG_COLOR, $messengerStyles->getBackgroundColor(), $brand)

            ->updateSetting(MSR::OPTIONS_TITLE, $messengerOptions->getTitle(), $brand)
            ->updateSetting(MSR::OPTIONS_AUTOSTART, $messengerOptions->isAutoStart(), $brand)
            ->updateSetting(MSR::OPTIONS_AUTOSTART_TIMEOUT, $messengerOptions->getAutoStartTimeout(), $brand)
            ->updateSetting(MSR::OPTIONS_SUBTEXT, $messengerOptions->getSubtext(), $brand)

            ->updateSetting(MSR::OPTIONS_TICKETS_TITLE, $optionsTickets->getTitle(), $brand)
            ->updateSetting(MSR::OPTIONS_TICKETS_BUTTON_TEXT, $optionsTickets->getButtonText(), $brand)
            ->updateSetting(MSR::OPTIONS_TICKETS_DESCRIPTION, $optionsTickets->getDescription(), $brand)

            ->updateSetting(MSR::OPTIONS_CHAT_SHOW_PHOTOS, $optionsChat->isShowAgentPhotos(), $brand)
            ->updateSetting(MSR::OPTIONS_CHAT_TITLE, $optionsChat->getTitle(), $brand)
            ->updateSetting(MSR::OPTIONS_CHAT_DESCRIPTION, $optionsChat->getDescription(), $brand)
            ->updateSetting(MSR::OPTIONS_CHAT_BUTTON_TEXT, $optionsChat->getButtonText(), $brand)
        ;
    }
}

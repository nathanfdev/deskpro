<?php

namespace DeskPRO\Bundle\MessengerBundle\Admin\Controller;

use Application\DeskPRO\Entity\Brand;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\Settings\AbstractBrandAwareSettingsController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Routing\RouterUtils;
use DeskPRO\Bundle\AppBundle\Settings\Model\AbstractBrandAwareSettings;
use DeskPRO\Bundle\MessengerBundle\Form\Type\Settings\MessengerType;
use DeskPRO\Bundle\MessengerBundle\Service\MessengerSettingsResolver as MSR;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerChat;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerSettings;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
     * You can use this endpoint to gather information about clients you need to obtain notifications and alerts.
     *
     * @ApiDoc(
     *     section="Messenger Setup",
     *     resourceDescription="Code action",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     output="string"
     * )
     * @Rest\Get("/code")
     *
     * @param Brand $brand
     *
     * @return View
     */
    public function getCodeAction(Brand $brand)
    {
        $assetUrl = $this->container->get('templating.helper.assets')->getUrl('', 'messenger_assets');
        $loaderJs = $this->container->get('templating.helper.assets')->getUrl('loader.js', 'messenger_loader_assets');
        $language = $this->container->get('language_stack')->getActiveOrDefault();
        $rootUrl  = $portalRouter  = $this->container->get('router');

        $baseSymfonyRouter = RouterUtils::unwrapDecoratedRouter($portalRouter);

        // $root_url is the url that the root index.php lives on
        $rootUrl = $baseSymfonyRouter->generate('portal_home', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $rootUrl = rtrim($rootUrl, '/');

        $code = <<<CODE
<script type="text/javascript">
    window.parent.DESKPRO_MESSENGER_ASSET_URL = "{$assetUrl}";
    window.parent.DESKPRO_MESSENGER_OPTIONS = {
      language: {
        id: "{$language->getId()}",
        locale: "{$language->getLocale()}"
      },
      helpdeskURL: "{$rootUrl}",
      baseUrl: "{$assetUrl}",
    }
</script>
<script id="dp-messenger-loader" src="{$loaderJs}" data-helpdesk-url="{$rootUrl}"></script>
CODE;

        return View::create($code, Response::HTTP_OK);
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
        if (isset($requestData['maxFileSize'])) {
            unset($requestData['maxFileSize']);
        }
        if (isset($requestData['translations'])) {
            unset($requestData['translations']);
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
        $messengerWidget             = $model->getWidget();
        $messengerEmbed              = $model->getEmbed();
        $messengerChat               = $model->getChat();
        $messengerChatOptions        = $model->getChat()->getOptions();
        $messengerChatPreChatForm    = $messengerChat->getPreChatForm();
        $messengerChatTicketDefaults = $messengerChat->getTicketDefaults();
        $messengerTickets            = $model->getTickets();
        $messengerProactive          = $model->getProactive();
        $messengerProactiveOptions   = $messengerProactive->getOptions();

        if (
            !$messengerTickets->isEnabled() &&
            $messengerChat->getNoAnswerBehavior() === MessengerChat::NO_ANSWER_CREATE_TICKET
        ) {
            $messengerChat->setNoAnswerBehavior(MessengerChat::NO_ANSWER_SAVE_TICKET);
        }
        $this
            ->getSettingRepository()

            // Widget Settings
            ->updateSetting(MSR::WIDGET_PRIMARY_COLOR, $messengerWidget->getPrimaryColor(), $brand)
            ->updateSetting(MSR::WIDGET_BG_COLOR, $messengerWidget->getBackgroundColor(), $brand)
            ->updateSetting(MSR::WIDGET_TEXT_COLOR, $messengerWidget->getTextColor(), $brand)
            ->updateSetting(MSR::WIDGET_POSITION, $messengerWidget->getPosition(), $brand)
            ->updateSetting(MSR::WIDGET_GREETING, $messengerWidget->getGreetingTitle(), $brand)

            // Chat settings
            ->updateSetting(MSR::CHAT_ENABLED, $messengerChat->isEnabled(), $brand)
            ->updateSetting(MSR::CHAT_DEFAULT_DEPARTMENT, $messengerChat->getDepartment(), $brand)
            ->updateSetting(MSR::CHAT_USERGROUPS, serialize($messengerChat->getUsergroups()), $brand)
            ->updateSetting(MSR::CHAT_PROMPT, $messengerChat->getPrompt(), $brand)
            ->updateSetting(MSR::CHAT_TIMEOUT, $messengerChat->getTimeout(), $brand)
            ->updateSetting(MSR::CHAT_NO_ANSWER_BEHAVIOR, $messengerChat->getNoAnswerBehavior(), $brand)
            ->updateSetting(MSR::CHAT_BUSY_MESSAGE, $messengerChat->getBusyMessage(), $brand)

            // Chat options
            ->updateSetting(MSR::CHAT_OPTIONS_SHOW_PHOTOS, $messengerChatOptions->isShowAgentPhotos(), $brand)

            // Pre-chat form
            ->updateSetting(MSR::PRE_CHAT_FORM_ENABLED, $messengerChatPreChatForm->isEnabled(), $brand)
            ->updateSetting(MSR::PRE_CHAT_FORM_NAME_ENABLED, $messengerChatPreChatForm->isNameEnabled(), $brand)
            ->updateSetting(MSR::PRE_CHAT_FORM_EMAIL_ENABLED, $messengerChatPreChatForm->isEmailEnabled(), $brand)
            ->updateSetting(MSR::PRE_CHAT_FORM_NAME_REQUIRED, $messengerChatPreChatForm->isNameRequired(), $brand)
            ->updateSetting(MSR::PRE_CHAT_FORM_EMAIL_REQUIRED, $messengerChatPreChatForm->isEmailRequired(), $brand)
            ->updateSetting(MSR::PRE_CHAT_FORM_DEPARTMENT, $messengerChatPreChatForm->isDepartmentSelectable(), $brand)
            ->updateSetting(MSR::PRE_CHAT_FORM_FIELDS, serialize($messengerChatPreChatForm->getFields()), $brand)
            ->updateSetting(MSR::PRE_CHAT_FORM_FORM_MESSAGE_ENABLED, $messengerChatPreChatForm->isFormMessageEnabled(), $brand)
            ->updateSetting(MSR::PRE_CHAT_FORM_FORM_MESSAGE, $messengerChatPreChatForm->getFormMessage(), $brand)

            // Chat settings. No answer behaviour === save as a ticket
            ->updateSetting(MSR::CHAT_TICKET_DEFAULTS_SUBJECT, $messengerChatTicketDefaults->getSubject(), $brand)
            ->updateSetting(MSR::CHAT_TICKET_DEFAULTS_SUBJECT_TYPE, $messengerChatTicketDefaults->getSubjectType(), $brand)
            ->updateSetting(MSR::CHAT_TICKET_DEFAULTS_DEP, $messengerChatTicketDefaults->getDepartment(), $brand)

            // Tickets settings
            ->updateSetting(MSR::TICKETS_ENABLED, $messengerTickets->isEnabled(), $brand)
            ->updateSetting(MSR::TICKETS_DEPARTMENT, $messengerTickets->getDepartment(), $brand)
            ->updateSetting(MSR::TICKETS_SUBJECT, $messengerTickets->getSubject(), $brand)
            ->updateSetting(MSR::TICKETS_DEPARTMENT_OPTION, $messengerTickets->getDepartmentOption(), $brand)
            ->updateSetting(MSR::TICKETS_SUBJECT_OPTION, $messengerTickets->getSubjectOption(), $brand)

            // These are proactive, defenitely.
            ->updateSetting(MSR::PROACTIVE_AUTOSTART, $messengerProactive->isAutoStart(), $brand)
            ->updateSetting(MSR::PROACTIVE_TIMEOUT, $messengerProactive->getAutoStartTimeout(), $brand)
            ->updateSetting(MSR::PROACTIVE_STYLE, $messengerProactive->getAutoStartStyle(), $brand)
            // These are proactive block config
            ->updateSetting(MSR::PROACTIVE_OPTIONS_GREETING_TITLE, $messengerProactiveOptions->getGreetingTitle(), $brand)
            ->updateSetting(MSR::PROACTIVE_OPTIONS_TITLE, $messengerProactiveOptions->getTitle(), $brand)
            ->updateSetting(MSR::PROACTIVE_OPTIONS_DESCRIPTION, $messengerProactiveOptions->getDescription(), $brand)
            ->updateSetting(MSR::PROACTIVE_OPTIONS_BUTTON_TEXT, $messengerProactiveOptions->getButtonText(), $brand)
            ->updateSetting(MSR::PROACTIVE_OPTIONS_INPUT_PLACEHOLDER, $messengerProactiveOptions->getInputPlaceholder(), $brand)

            // Add Widget & Chat section
            ->updateSetting(MSR::EMBED_AUTHORIZE_DOMAINS, $messengerEmbed->getAuthorizeDomains(), $brand)
            ->updateSetting(MSR::EMBED_ENABLED_ON_PORTAL, $messengerEmbed->isShowOnPortal(), $brand)
            ->updateSetting(MSR::JWT_SECRET, $messengerEmbed->getJwtSecret(), $brand)

        ;
    }
}

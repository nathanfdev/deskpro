<?php

namespace DeskPRO\Bundle\MessengerBundle\Admin\Controller;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Phrase;
use Application\DeskPRO\Translate\Translate;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\Settings\AbstractBrandAwareSettingsController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
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
    public function getCodeAction(Brand $brand, Request $request = null)
    {
        if ($request && $request->attributes->has('_dp_brand_slug')) {
            if ($request && $request->attributes->has('original_request')) {
                $baseUrl = $this->originalUrlGenerator->generate(
                    $request->attributes->get('original_request'),
                    'portal_home',
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
            } else {
                $baseUrl = $this->container->get('router')->generate('portal_home', [], UrlGeneratorInterface::ABSOLUTE_URL);
            }

            if (empty($this->getSetting('core.deskpro_url', $brand))) {
                $helpdeskUrl = $this->container->get('router')->generate(
                    'portal_home',
                    ['brand' => $brand],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
            } else {
                $helpdeskUrl = rtrim($baseUrl, '/').$request->attributes->get('_dp_brand_slug_path');
            }
        } else {
            $helpdeskUrl = $this->container->get('router')->generate('portal_home', ['brand' => $brand], UrlGeneratorInterface::ABSOLUTE_URL);

            if ($brand->getUrl()) {
                $baseUrl = $helpdeskUrl;
            } else {
                // brand has just a slug, use default brand
                $baseUrl = $this->container->get('router')->generate('portal_home', [], UrlGeneratorInterface::ABSOLUTE_URL);
            }
        }

        if ($request) {
            $urlCorrector = $this->container->get('url_corrector_factory')->createUrlCorrector($brand);
            $helpdeskUrl  = $urlCorrector->forceCorrectUrlScheme($helpdeskUrl, $request);
        }

        $correctAssetUrl = function ($assetUrl) use ($baseUrl, $request, $brand) {
            $basePath = $request ? $request->getBasePath() : '';

            if (!preg_match('#^https?://#i', $assetUrl)) {
                $assetUrl = rtrim(str_replace($basePath, '', $baseUrl), '/').$assetUrl;
            }
            if ($request) {
                $urlCorrector = $this->container->get('url_corrector_factory')->createUrlCorrector($brand);
                $assetUrl     = $urlCorrector->forceCorrectUrlScheme($assetUrl, $request);
            }

            return $assetUrl;
        };

        $assetUrl = $this->container->get('templating.helper.assets')->getUrl('', 'messenger_assets');
        $loaderJS = $this->container->get('templating.helper.assets')->getUrl('loader.js', 'messenger_loader_assets');
        $loaderJS = $correctAssetUrl($loaderJS);
        $assetUrl = $correctAssetUrl($assetUrl);

        $language = $this->container->get('language_stack')->getActiveOrDefault();

        $baseUrl = rtrim($baseUrl, '/');

        $code = <<<CODE
<!--DESKPRO_WIDGET_LOADER::BEGIN-->
<script type="text/javascript">
    window.parent.DESKPRO_MESSENGER_ASSET_URL = "{$assetUrl}";
    window.parent.DESKPRO_MESSENGER_OPTIONS = {
      language: {
        id: "{$language->getId()}",
        locale: "{$language->getLocale()}"
      },
      helpdeskURL: "{$baseUrl}",
      baseUrl: "{$assetUrl}",
    }
</script>
<script id="dp-messenger-loader" src="{$loaderJS}"></script>
<!--DESKPRO_WIDGET_LOADER::END-->
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
            $this->updateTranslations($requestData['translations'], $model);
            unset($requestData['translations']);
        }
        $form->submit($requestData);
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $this->persistModel($model);
    }

    private function updateTranslations($translations, AbstractBrandAwareSettings $model)
    {
        /** @var Translate $translate */
        $translate = $this->container->get('deskpro.core.translate');

        foreach ($translations as $phraseName => $translationStack) {
            $phraseName = str_replace('helpcenter_messenger_', 'helpcenter.messenger.', $phraseName);
            foreach ($translationStack as $translation) {
                $language = $this->get('language_manager')->getLanguageById($translation['language']['id']);
                $text     = trim($translation['text']);
                if ($text != $translate->getPhraseText($phraseName, $language, true)) {
                    $updateVersion = true;
                    $phrase        = $this->getManager()->getRepository(Phrase::class)->getPhraseForLanguage($phraseName, $language);
                    if (!$text) {
                        if ($phrase) {
                            $this->getManager()->remove($phrase);
                        }
                    } else {
                        if (!$phrase) {
                            $phrase = new Phrase();
                            $phrase->setLanguage($language);
                            $phrase->setName($phraseName);
                            $phrase->setOriginalPhrase('');
                            $phrase->setOriginalHash(md5(null));
                        }

                        if (!$phrase->getOriginalPhrase()) {
                            $phrase->setOriginalPhrase('');
                            $phrase->setOriginalHash(md5(null));
                        }
                        $phrase->setPhrase($text);
                        $this->getManager()->persist($phrase);
                    }
                }
            }
        }
        $this->getManager()->flush();
        if ($updateVersion) {
            $this
                ->getSettingRepository()
                ->updateSetting(MSR::WIDGET_LANG_VERSION, time(), $model->getBrand());
        }
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
        $icon                        = $messengerWidget->getIcon();

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
            ->updateSetting(
                MSR::WIDGET_ICON,
                $icon ? $icon->setIsTemp(false)->getId() : null,
                $brand
            )

            // Chat settings
            ->updateSetting(MSR::CHAT_ENABLED, $messengerChat->isEnabled(), $brand)
            ->updateSetting(MSR::CHAT_DEFAULT_DEPARTMENT, $messengerChat->getDepartment(), $brand)
            ->updateSetting(MSR::CHAT_USERGROUPS, serialize($messengerChat->getUsergroups()), $brand)
            ->updateSetting(MSR::CHAT_TIMEOUT, $messengerChat->getTimeout(), $brand)
            ->updateSetting(MSR::CHAT_NO_ANSWER_BEHAVIOR, $messengerChat->getNoAnswerBehavior(), $brand)

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

            // Add Widget & Chat section
            ->updateSetting(MSR::EMBED_AUTHORIZE_DOMAINS, $messengerEmbed->getAuthorizeDomains(), $brand)
            ->updateSetting(MSR::EMBED_ENABLED_ON_PORTAL, $messengerEmbed->isShowOnPortal(), $brand)
            ->updateSetting(MSR::JWT_SECRET, $messengerEmbed->getJwtSecret(), $brand)
        ;

        $this->container->get('doctrine.orm.default_entity_manager')->flush();
    }
}

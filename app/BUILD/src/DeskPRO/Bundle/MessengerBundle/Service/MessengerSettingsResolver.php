<?php

namespace DeskPRO\Bundle\MessengerBundle\Service;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Department;
use DeskPRO\Bundle\AppBundle\Settings\AbstractBrandAwareSettingsResolver;
use DeskPRO\Bundle\AppBundle\Settings\BrandAwareSettingsResolver;
use DeskPRO\Bundle\AppBundle\Settings\WidgetSettingsResolver;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerChat;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerChatOptions;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerChatTicketDefaults;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerEmbed;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerOptions;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerOptionsProactive;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerOptionsTickets;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerSettings;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerTickets;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerWidget;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\PreChatForm;
use Doctrine\ORM\EntityManager;
use Orb\Util\Env;

/**
 * Class MessengerSettingsResolver.
 */
class MessengerSettingsResolver extends AbstractBrandAwareSettingsResolver
{
    // just fo the bc and code reuse
    const JWT_SECRET              = WidgetSettingsResolver::JWT_SECRET;
    const EMBED_ENABLED_ON_PORTAL = 'messenger.embed.show_on_portal';
    const EMBED_AUTHORIZE_DOMAINS = 'messenger.embed.authorize_domains';

    const TICKETS_ENABLED           = 'messenger.tickets.enabled';
    const TICKETS_SUBJECT           = 'messenger.tickets.subject';
    const TICKETS_DEPARTMENT        = 'messenger.tickets.department';
    const TICKETS_DEPARTMENT_OPTION = 'messenger.tickets.department_option';

    const CHAT_ENABLED              = 'messenger.chat.enabled';
    const CHAT_DEFAULT_DEPARTMENT   = 'messenger.chat.department';
    const CHAT_USERGROUPS           = 'messenger.chat.usergroups';
    const CHAT_PROMPT               = 'messenger.chat.prompt';
    const CHAT_TIMEOUT              = 'messenger.chat.timeout';
    const CHAT_NO_ANSWER_BEHAVIOR   = 'messenger.chat.no_answer';
    const CHAT_BUSY_MESSAGE         = 'messenger.chat.busy';

    const CHAT_OPTIONS_TITLE       = 'messenger.chat.options.title';
    const CHAT_OPTIONS_BUTTON_TEXT = 'messenger.chat.options.button_text';
    const CHAT_OPTIONS_DESCRIPTION = 'messenger.chat.options.description';
    const CHAT_OPTIONS_SHOW_PHOTOS = 'messenger.chat.options.show_photos';

    const CHAT_TICKET_DEFAULTS_SUBJECT      = 'messenger.chat.ticket_defaults.subject';
    const CHAT_TICKET_DEFAULTS_SUBJECT_TYPE = 'messenger.chat.ticket_defaults.subject_type';
    const CHAT_TICKET_DEFAULTS_DEP          = 'messenger.chat.ticket_defaults.department';

    const PRE_CHAT_FORM_ENABLED              = 'messenger.chat.pre_chat_form.enabled';
    const PRE_CHAT_FORM_NAME_ENABLED         = 'messenger.chat.pre_chat_form.name.enabled';
    const PRE_CHAT_FORM_EMAIL_ENABLED        = 'messenger.chat.pre_chat_form.email.enabled';
    const PRE_CHAT_FORM_NAME_REQUIRED        = 'messenger.chat.pre_chat_form.name.required';
    const PRE_CHAT_FORM_EMAIL_REQUIRED       = 'messenger.chat.pre_chat_form.email.required';
    const PRE_CHAT_FORM_DEPARTMENT           = 'messenger.chat.pre_chat_form.department';
    const PRE_CHAT_FORM_FIELDS               = 'messenger.chat.pre_chat_form.fields';
    const PRE_CHAT_FORM_FORM_MESSAGE_ENABLED = 'messenger.chat.pre_chat_form.form_message_enabled';
    const PRE_CHAT_FORM_FORM_MESSAGE         = 'messenger.chat.pre_chat_form.form_message';

    const WIDGET_PRIMARY_COLOR = 'messenger.widget.primary_color';
    const WIDGET_BG_COLOR      = 'messenger.widget.bg_color';
    const WIDGET_TEXT_COLOR    = 'messenger.widget.text_color';
    const WIDGET_POSITION      = 'messenger.widget.position';
    const WIDGET_GREETING      = 'messenger.widget.greeting';

    const OPTIONS_AUTOSTART         = 'messenger.options.autostart';
    const OPTIONS_AUTOSTART_TIMEOUT = 'messenger.options.autostart_timeout';
    const OPTIONS_AUTOSTART_STYLE   = 'messenger.options.autostart_style';
    const OPTIONS_SUBTEXT           = 'messenger.options.subtext';
    const OPTIONS_TITLE             = 'messenger.options.title';

    const OPTIONS_PROACTIVE_GREETING_TITLE    = 'messenger.options.proactive.greeting_title';
    const OPTIONS_PROACTIVE_TITLE             = 'messenger.options.proactive.title';
    const OPTIONS_PROACTIVE_BUTTON_TEXT       = 'messenger.options.proactive.button_text';
    const OPTIONS_PROACTIVE_INPUT_PLACEHOLDER = 'messenger.options.proactive.input_placeholder';
    const OPTIONS_PROACTIVE_DESCRIPTION       = 'messenger.options.proactive.description';

    const OPTIONS_TICKETS_TITLE       = 'messenger.options.tickets.title';
    const OPTIONS_TICKETS_BUTTON_TEXT = 'messenger.options.tickets.button_text';
    const OPTIONS_TICKETS_DESCRIPTION = 'messenger.options.tickets.description';

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param BrandAwareSettingsResolver $settingsResolver
     * @param EntityManager              $em
     */
    public function __construct(
        BrandAwareSettingsResolver $settingsResolver,
        EntityManager $em
    ) {
        parent::__construct($settingsResolver);
        $this->em = $em;
    }

    /**
     * @param Brand $brand
     *
     * @return MessengerSettings
     */
    public function getMessengerSettings(Brand $brand)
    {
        $model = new MessengerSettings();

        return $model
            ->setBrand($brand)
            ->setWidget($this->getMessengerWidget($brand))
            ->setChat($this->getMessengerChat($brand))
            ->setEmbed($this->getMessengerEmbedSettings($brand))
            ->setTickets($this->getMessengerTickets($brand))
            ->setMessenger($this->getMessengerOptions($brand))
        ;
    }

    /**
     * @param Brand $brand
     *
     * @return MessengerEmbed
     */
    protected function getMessengerEmbedSettings(Brand $brand)
    {
        $embed = new MessengerEmbed();
        $embed
            ->setAuthorizeDomains($this->getSettings(self::EMBED_AUTHORIZE_DOMAINS, $brand, $embed->getAuthorizeDomains()))
            ->setShowOnPortal($this->getSettings(self::EMBED_ENABLED_ON_PORTAL, $brand, $embed->isShowOnPortal()))
            ->setJwtSecret($this->getSettings(self::JWT_SECRET, $brand, $embed->getJwtSecret()))
        ;

        return $embed;
    }

    /**
     * @param Brand $brand
     *
     * @return MessengerTickets
     */
    protected function getMessengerTickets(Brand $brand)
    {
        $mTickets = new MessengerTickets();

        return $mTickets
            ->setEnabled($this->getSettings(self::TICKETS_ENABLED, $brand, $mTickets->isEnabled()))
            ->setSubject($this->getSettings(self::TICKETS_SUBJECT, $brand, $mTickets->getSubject()))
            ->setDepartment($this->getSettings(self::TICKETS_DEPARTMENT, $brand, $this->getDefaultDepartment('ticket')))
            ->setDepartmentOption($this->getSettings(self::TICKETS_DEPARTMENT_OPTION, $brand, $mTickets->getDepartmentOption()))
            ;
    }

    /**
     * @param Brand $brand
     *
     * @return MessengerChat
     */
    protected function getMessengerChat(Brand $brand)
    {
        $mChat = new MessengerChat();

        return $mChat
            ->setEnabled($this->getSettings(self::CHAT_ENABLED, $brand, $mChat->isEnabled()))
            ->setDepartment($this->getSettings(self::CHAT_DEFAULT_DEPARTMENT, $brand, $this->getDefaultDepartment('chat')))
            ->setUsergroups(unserialize($this->getSettings(self::CHAT_USERGROUPS, $brand, serialize($mChat->getUsergroups()))))
            ->setPrompt($this->getSettings(self::CHAT_PROMPT, $brand, $mChat->getPrompt()))
            ->setOptions($this->getMessengerChatOptions($brand))
            ->setPreChatForm($this->getPreChatForm($brand))
            ->setTimeout($this->getSettings(self::CHAT_TIMEOUT, $brand, $mChat->getTimeout()))
            ->setNoAnswerBehavior($this->getSettings(self::CHAT_NO_ANSWER_BEHAVIOR, $brand, $mChat->getNoAnswerBehavior()))
            ->setBusyMessage($this->getSettings(self::CHAT_BUSY_MESSAGE, $brand, $mChat->getBusyMessage()))
            ->setTicketDefaults($this->getMessengerChatTicketDefaults($brand))
        ;
    }

    /**
     * @param Brand $brand
     *
     * @return MessengerChatTicketDefaults
     */
    protected function getMessengerChatTicketDefaults(Brand $brand)
    {
        $mChatTicketDefaults = new MessengerChatTicketDefaults();

        return $mChatTicketDefaults
            ->setSubject($this->getSettings(self::CHAT_TICKET_DEFAULTS_SUBJECT, $brand, $mChatTicketDefaults->getSubject()))
            ->setSubjectType($this->getSettings(self::CHAT_TICKET_DEFAULTS_SUBJECT_TYPE, $brand, $mChatTicketDefaults->getSubjectType()))
            ->setDepartment($this->getSettings(self::CHAT_TICKET_DEFAULTS_DEP, $brand, $this->getDefaultDepartment('ticket')))
            ;
    }

    /**
     * @param Brand $brand
     *
     * @return PreChatForm
     */
    protected function getPreChatForm(Brand $brand)
    {
        $mPreChatForm = new PreChatForm();

        return $mPreChatForm
            ->setEnabled($this->getSettings(self::PRE_CHAT_FORM_ENABLED, $brand, $mPreChatForm->isEnabled()))
            ->setIsNameEnabled($this->getSettings(self::PRE_CHAT_FORM_NAME_ENABLED, $brand, $mPreChatForm->isNameEnabled()))
            ->setIsEmailEnabled($this->getSettings(self::PRE_CHAT_FORM_EMAIL_ENABLED, $brand, $mPreChatForm->isEmailEnabled()))
            ->setIsNameRequired($this->getSettings(self::PRE_CHAT_FORM_NAME_REQUIRED, $brand, $mPreChatForm->isNameRequired()))
            ->setIsEmailRequired($this->getSettings(self::PRE_CHAT_FORM_EMAIL_REQUIRED, $brand, $mPreChatForm->isEmailRequired()))
            ->setIsDepartmentSelectable($this->getSettings(self::PRE_CHAT_FORM_DEPARTMENT, $brand, $mPreChatForm->isDepartmentSelectable()))
            ->setFormMessageEnabled($this->getSettings(self::PRE_CHAT_FORM_FORM_MESSAGE_ENABLED, $brand, $mPreChatForm->isFormMessageEnabled()))
            ->setFormMessage($this->getSettings(self::PRE_CHAT_FORM_FORM_MESSAGE, $brand, $mPreChatForm->getFormMessage()))
            ->setFields(unserialize($this->getSettings(self::PRE_CHAT_FORM_FIELDS, $brand, serialize($mPreChatForm->getFields()))))
        ;
    }

    /**
     * @param Brand $brand
     *
     * @return MessengerWidget
     */
    protected function getMessengerWidget(Brand $brand)
    {
        $messengerWidget = new MessengerWidget();

        return $messengerWidget
            ->setPrimaryColor($this->getSettings(self::WIDGET_PRIMARY_COLOR, $brand, $messengerWidget->getPrimaryColor()))
            ->setBackgroundColor($this->getSettings(self::WIDGET_BG_COLOR, $brand, $messengerWidget->getBackgroundColor()))
            ->setTextColor($this->getSettings(self::WIDGET_TEXT_COLOR, $brand, $messengerWidget->getTextColor()))
            ->setPosition($this->getSettings(self::WIDGET_POSITION, $brand, $messengerWidget->getPosition()))
            ->setGreetingTitle($this->getSettings(self::WIDGET_GREETING, $brand, $messengerWidget->getGreetingTitle()))
        ;
    }

    /**
     * @param Brand $brand
     *
     * @return MessengerOptions
     */
    protected function getMessengerOptions(Brand $brand)
    {
        $mOptions = new MessengerOptions();

        return $mOptions
            ->setMaxFileSize(min(Env::getEffectiveMaxUploadSize(), $this->getSettings('core.attach_user_maxsize', null, 1024 * 1024 * 10)))
            ->setAutoStart($this->getSettings(self::OPTIONS_AUTOSTART, $brand, $mOptions->isAutoStart()))
            ->setAutoStartTimeout($this->getSettings(self::OPTIONS_AUTOSTART_TIMEOUT, $brand, $mOptions->getAutoStartTimeout()))
            ->setAutoStartStyle($this->getSettings(self::OPTIONS_AUTOSTART_STYLE, $brand, $mOptions->getAutoStartStyle()))
            ->setProactive($this->getMessengerOptionsProactive($brand))
            ->setTickets($this->getMessengerOptionsTickets($brand))
        ;
    }

    /**
     * @param Brand $brand
     *
     * @return MessengerChatOptions
     */
    protected function getMessengerChatOptions(Brand $brand)
    {
        $mChatOptions = new MessengerChatOptions();

        return $mChatOptions
            ->setTitle($this->getSettings(self::CHAT_OPTIONS_TITLE, $brand, $mChatOptions->getTitle()))
            ->setButtonText($this->getSettings(self::CHAT_OPTIONS_BUTTON_TEXT, $brand, $mChatOptions->getButtonText()))
            ->setDescription($this->getSettings(self::CHAT_OPTIONS_DESCRIPTION, $brand, $mChatOptions->getDescription()))
            ->setShowAgentPhotos($this->getSettings(self::CHAT_OPTIONS_SHOW_PHOTOS, $brand, $mChatOptions->isShowAgentPhotos()))
        ;
    }

    /**
     * @param Brand $brand
     *
     * @return MessengerOptionsProactive
     */
    protected function getMessengerOptionsProactive(Brand $brand)
    {
        $mOptionsProactive = new MessengerOptionsProactive();

        return $mOptionsProactive
            ->setGreetingTitle($this->getSettings(self::OPTIONS_PROACTIVE_GREETING_TITLE, $brand, $mOptionsProactive->getGreetingTitle()))
            ->setTitle($this->getSettings(self::OPTIONS_PROACTIVE_TITLE, $brand, $mOptionsProactive->getTitle()))
            ->setButtonText($this->getSettings(self::OPTIONS_PROACTIVE_BUTTON_TEXT, $brand, $mOptionsProactive->getButtonText()))
            ->setInputPlaceholder($this->getSettings(self::OPTIONS_PROACTIVE_INPUT_PLACEHOLDER, $brand, $mOptionsProactive->getInputPlaceholder()))
            ->setDescription($this->getSettings(self::OPTIONS_PROACTIVE_DESCRIPTION, $brand, $mOptionsProactive->getDescription()))
            ;
    }

    /**
     * @param Brand $brand
     *
     * @return MessengerOptionsTickets
     */
    protected function getMessengerOptionsTickets(Brand $brand)
    {
        $mOptionsTickets = new MessengerOptionsTickets();

        return $mOptionsTickets
            ->setTitle($this->getSettings(self::OPTIONS_TICKETS_TITLE, $brand, $mOptionsTickets->getTitle()))
            ->setButtonText($this->getSettings(self::OPTIONS_TICKETS_BUTTON_TEXT, $brand, $mOptionsTickets->getButtonText()))
            ->setDescription($this->getSettings(self::OPTIONS_TICKETS_DESCRIPTION, $brand, $mOptionsTickets->getDescription()))
            ;
    }

    /**
     * @param string $name
     * @param Brand  $brand
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getSettings($name, Brand $brand = null, $default = null)
    {
        return $this->settingsResolver->getSetting($name, $brand, $default);
    }

    private function getDefaultDepartment($type = 'chat')
    {
        $department = $this->em->getRepository(Department::class)->getDefaultDepartment($type);

        return $department ? $department->getId() : 0;
    }
}

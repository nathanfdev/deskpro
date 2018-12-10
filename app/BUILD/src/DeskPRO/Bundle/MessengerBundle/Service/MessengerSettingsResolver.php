<?php

namespace DeskPRO\Bundle\MessengerBundle\Service;

use Application\DeskPRO\Entity\Brand;
use DeskPRO\Bundle\AppBundle\Settings\AbstractBrandAwareSettingsResolver;
use DeskPRO\Bundle\AppBundle\Settings\BrandAwareSettingsResolver;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerChat;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerEmbed;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerOptions;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerOptionsChat;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerOptionsTickets;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerSettings;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerStyles;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerTickets;
use Doctrine\ORM\EntityManager;

/**
 * Class MessengerSettingsResolver.
 */
class MessengerSettingsResolver extends AbstractBrandAwareSettingsResolver
{
    const EMBED_ENABLED_ON_PORTAL = 'messenger.embed.show_on_portal';
    const EMBED_AUTHORIZE_DOMAINS = 'messenger.embed.authorize_domains';

    const TICKETS_ENABLED = 'messenger.tickets.enabled';

    const CHAT_ENABLED            = 'messenger.chat.enabled';
    const CHAT_PROMPT             = 'messenger.chat.prompt';
    const CHAT_TIMEOUT            = 'messenger.chat.timout';
    const CHAT_NO_ANSWER_BEHAVIOR = 'messenger.chat.no_answer';
    const CHAT_BUSY_MESSAGE       = 'messenger.chat.busy';
    const CHAT_DEFAULT_DEPARTMENT = 'messenger.chat.department';
    const CHAT_TICKET_SUBJECT     = 'messenger.chat.ticket_subject';

    const STYLE_BG_COLOR      = 'messenger.styles.bg_color';
    const STYLE_PRIMARY_COLOR = 'messenger.styles.primary_color';

    const OPTIONS_AUTOSTART = 'messenger.options.autostart';
    const OPTIONS_SUBTEXT   = 'messenger.options.subtext';
    const OPTIONS_TITLE     = 'messenger.options.title';

    const OPTIONS_CHAT_TITLE       = 'messenger.options.chat.title';
    const OPTIONS_CHAT_BUTTON_TEXT = 'messenger.options.chat.button_text';
    const OPTIONS_CHAT_DESCRIPTION = 'messenger.options.chat.description';
    const OPTIONS_CHAT_SHOW_PHOTOS = 'messenger.options.chat.show_photos';

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
        EntityManager              $em
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
            ->setEmbed($this->getMessengerEmbedSettings($brand))
            ->setStyles($this->getMessengerStyles($brand))
            ->setTickets($this->getMessengerTickets($brand))
            ->setChat($this->getMessengerChat($brand))
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

        return $mTickets->setEnabled($this->getSettings(self::TICKETS_ENABLED, $brand, $mTickets->isEnabled()));
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
            ->setBusyMessage($this->getSettings(self::CHAT_BUSY_MESSAGE, $brand, $mChat->getBusyMessage()))
            ->setDepartment($this->getSettings(self::CHAT_DEFAULT_DEPARTMENT, $brand, $mChat->getDepartment()))
            ->setNoAnswerBehavior($this->getSettings(self::CHAT_NO_ANSWER_BEHAVIOR, $brand, $mChat->getNoAnswerBehavior()))
            ->setPrompt($this->getSettings(self::CHAT_PROMPT, $brand, $mChat->getPrompt()))
            ->setPrompt($this->getSettings(self::CHAT_TIMEOUT, $brand, $mChat->getTimeout()))
            ->setPrompt($this->getSettings(self::CHAT_TICKET_SUBJECT, $brand, $mChat->getTicketSubject()))
            ;
    }

    /**
     * @param Brand $brand
     *
     * @return MessengerStyles
     */
    protected function getMessengerStyles(Brand $brand)
    {
        $mStyles = new MessengerStyles();

        return $mStyles
            ->setBackgroundColor($this->getSettings(self::STYLE_BG_COLOR, $brand, $mStyles->getBackgroundColor()))
            ->setPrimaryColor($this->getSettings(self::STYLE_PRIMARY_COLOR, $brand, $mStyles->getPrimaryColor()))
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
            ->setAutoStart($this->getSettings(self::OPTIONS_AUTOSTART, $brand, $mOptions->isAutoStart()))
            ->setSubtext($this->getSettings(self::OPTIONS_SUBTEXT, $brand, $mOptions->getSubtext()))
            ->setTitle($this->getSettings(self::OPTIONS_TITLE, $brand, $mOptions->getTitle()))
            ->setChat($this->getMessengerOptionsChat($brand))
            ->setTickets($this->getMessengerOptionsTickets($brand))
            ;
    }

    /**
     * @param Brand $brand
     *
     * @return MessengerOptionsChat
     */
    protected function getMessengerOptionsChat(Brand $brand)
    {
        $mOptionsChat = new MessengerOptionsChat();

        return $mOptionsChat
            ->setTitle($this->getSettings(self::OPTIONS_CHAT_TITLE, $brand, $mOptionsChat->getTitle()))
            ->setButtonText($this->getSettings(self::OPTIONS_CHAT_BUTTON_TEXT, $brand, $mOptionsChat->getButtonText()))
            ->setDescription($this->getSettings(self::OPTIONS_CHAT_DESCRIPTION, $brand, $mOptionsChat->getDescription()))
            ->setShowAgentPhotos($this->getSettings(self::OPTIONS_CHAT_SHOW_PHOTOS, $brand, $mOptionsChat->isShowAgentPhotos()))
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
            ->setTitle($this->getSettings(self::OPTIONS_CHAT_TITLE, $brand, $mOptionsTickets->getTitle()))
            ->setButtonText($this->getSettings(self::OPTIONS_CHAT_BUTTON_TEXT, $brand, $mOptionsTickets->getButtonText()))
            ->setDescription($this->getSettings(self::OPTIONS_CHAT_DESCRIPTION, $brand, $mOptionsTickets->getDescription()))
            ;
    }

    /**
     * @param string $name
     * @param Brand  $brand
     * @param mixed  $default
     *
     * @return mixed
     */
    protected function getSettings($name, Brand $brand, $default = null)
    {
        return $this->settingsResolver->getSetting($name, $brand, $default);
    }
}

<?php

namespace DeskPRO\Bundle\MessengerBundle\Service;

use Application\DeskPRO\Entity\Brand;
use DeskPRO\Bundle\AppBundle\Settings\AbstractBrandAwareSettingsResolver;
use DeskPRO\Bundle\AppBundle\Settings\BrandAwareSettingsResolver;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerChat;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerEmbed;
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
        $model
            ->setEmbed($this->getMessengerEmbedSettings($brand))
        ;

        return $model;
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

    protected function getMessengerStyles(Brand $brand)
    {
        $mStyles = new MessengerStyles();

        return $mStyles
            ->setBackgroundColor($this->getSettings(self::STYLE_BG_COLOR, $brand, $mStyles->getBackgroundColor()))
            ->setPrimaryColor($this->getSettings(self::STYLE_PRIMARY_COLOR, $brand, $mStyles->getPrimaryColor()))
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

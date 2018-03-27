<?php

namespace DeskPRO\Bundle\PortalBundle\Model;

use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\GlobalSettings\WidgetGlobalSettings;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class WidgetSession.
 */
class WidgetSession
{
    /**
     * Widget global settings.
     *
     * @var WidgetGlobalSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\GlobalSettings\WidgetGlobalSettings")
     */
    private $globalSettings;

    /**
     * Widget person.
     *
     * @var Person
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     */
    private $person;

    /**
     * Is chat granted.
     *
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $isChatGranted;

    /**
     * @var Language
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Language>")
     */
    private $language;

    /**
     * @var int
     *
     * @JMS\Type("string")
     */
    private $chatId;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $buildNum;

    /**
     * Constructor.
     *
     * @param TokenInterface       $token
     * @param WidgetGlobalSettings $globalSettings
     * @param bool                 $isChatGranted
     * @param Language             $defaultLanguage
     * @param int                  $chatId
     * @param int                  $buildNum
     */
    public function __construct(
        TokenInterface       $token,
        WidgetGlobalSettings $globalSettings,
        $isChatGranted,
        Language $defaultLanguage,
        $chatId,
        $buildNum
    ) {
        $this->person         = $token->getUser() instanceof Person ? $token->getUser() : null;
        $this->globalSettings = $globalSettings;
        $this->isChatGranted  = $isChatGranted;
        $this->chatId         = $chatId;
        $this->buildNum       = $buildNum;

        if ($this->person && $this->person->getLanguage()) {
            $this->language = $this->person->getLanguage();
        } else {
            $this->language = $defaultLanguage;
        }
    }
}

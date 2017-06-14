<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\AccountInfo;

use Application\DeskPRO\Entity\Language;
use JMS\Serializer\Annotation as JMS;

/**
 * Class AccountInfo.
 */
class AccountInfo
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $timezone;

    /**
     * Default person`s language.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Language>")
     *
     * @var Language
     */
    private $language;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $signatureHtml;

    /**
     * @param string $timezone
     *
     * @return $this
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * @param Language $language
     *
     * @return $this
     */
    public function setLanguage(Language $language = null)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @param string $html
     *
     * @return $this
     */
    public function setSignatureHtml($html)
    {
        $this->signatureHtml = $html;

        return $this;
    }
}

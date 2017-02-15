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

namespace DeskPRO\Bundle\AppBundle\Entity\VoiceAsset;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class VoiceTextAsset.
 *
 * @JMS\ExclusionPolicy("all")
 * @ORM\Entity
 */
class VoiceTextAsset extends AbstractVoiceAsset
{
    /**
     * @ORM\Column(name="text", type="text")
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $text = '';

    /**
     * @ORM\Column(name="language", type="text")
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     * @Assert\Choice(choices={
     *     "da-DK",
     *     "de-DE",
     *     "en-AU",
     *     "en-CA",
     *     "en-GB",
     *     "en-IN",
     *     "en-US",
     *     "ca-ES",
     *     "es-ES",
     *     "es-MX",
     *     "fi-FI",
     *     "fr-CA",
     *     "fr-FR",
     *     "it-IT",
     *     "ja-JP",
     *     "ko-KR",
     *     "nb-NO",
     *     "nl-NL",
     *     "pl-PL",
     *     "pt-BR",
     *     "pt-PT",
     *     "ru-RU",
     *     "sv-SE",
     *     "zh-CN",
     *     "zh-HK",
     *     "zh-TW"
     * })
     *
     * @var string
     */
    protected $language = '';

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return AbstractVoiceAsset::TYPE_TEXT;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     *
     * @return $this
     */
    public function setText($text)
    {
        $this->setModelField('text', $text);

        return $this;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param string $language
     *
     * @return $this
     */
    public function setLanguage($language)
    {
        $this->setModelField('language', $language);

        return $this;
    }
}

<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Blob;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

/**
 * Class VoiceAsset.
 *
 * @ORM\Entity()
 * @ORM\Table(name="voice_assets")
 *
 * @JMS\ExclusionPolicy("all")
 *
 * @Assert\GroupSequenceProvider
 */
class VoiceAsset implements EntityInterface, NotifyPropertyChanged, GroupSequenceProviderInterface
{
    use NotifyPropertyChangedTrait;

    /**
     * The unique ID.
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(name="type", type="string", length=255)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     * @Assert\Choice(choices={"text", "record", "upload"})
     *
     * @var string
     */
    private $type;

    /**
     * @ORM\Column(name="text", type="text")
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @Assert\NotBlank(groups={"text"})
     *
     * @var string
     */
    private $text = '';

    /**
     * @ORM\Column(name="language", type="text")
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @Assert\NotBlank(groups={"text"})
     * @Assert\Choice(groups={"text"}, choices={
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
    private $language = '';

    /**
     * @ORM\OneToOne(targetEntity="Application\DeskPRO\Entity\Blob", cascade={"persist", "remove"})
     *
     * @JMS\Expose()
     * @JMS\Type("Application\DeskPRO\Entity\Blob")
     *
     * @Assert\NotBlank(groups={"blob"})
     *
     * @var Blob
     */
    private $blob;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->setModelField('name', $name);

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->setModelField('type', $type);

        return $this;
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

    /**
     * @return Blob
     */
    public function getBlob()
    {
        return $this->blob;
    }

    /**
     * @param Blob $blob
     *
     * @return $this
     */
    public function setBlob($blob)
    {
        $this->setModelField('blob', $blob);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupSequence()
    {
        $groups = ['VoiceAsset'];

        if ($this->type === 'text') {
            $groups[] = 'text';
        } elseif (in_array($this->type, ['upload', 'record'])) {
            $groups[] = 'blob';
        }

        return $groups;
    }
}

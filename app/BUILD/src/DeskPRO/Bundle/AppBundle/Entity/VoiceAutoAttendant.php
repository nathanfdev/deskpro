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

namespace DeskPRO\Bundle\AppBundle\Entity;

use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class VoiceAutoAttendant.
 *
 * @ORM\Entity()
 * @ORM\Table(name="voice_auto_attendants")
 *
 * @JMS\ExclusionPolicy("all")
 */
class VoiceAutoAttendant implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * The unique ID.
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
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
     * @ORM\OneToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\VoiceAsset", cascade={"persist", "remove"}, fetch="EAGER")
     *
     * @JMS\Expose()
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Entity\VoiceAsset")
     *
     * @Assert\Valid()
     *
     * @var VoiceAsset
     */
    private $audioAsset;

    /**
     * @ORM\OneToMany(
     *     targetEntity="DeskPRO\Bundle\AppBundle\Entity\VoiceAutoAttendantDialNumber",
     *     mappedBy="voiceAutoAttendant",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     *
     * @AppAssert\UniqueCollection(property="dialNum")
     *
     * @var VoiceAutoAttendantDialNumber[]|ArrayCollection
     */
    private $dialNumbers;

    /**
     * @ORM\Column(name="allow_repeat_menu", type="boolean")
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $allowRepeatMenu = false;

    /**
     * @ORM\Column(name="allow_extension", type="boolean")
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $allowExtension = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->dialNumbers = new ArrayCollection();
    }

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
     * @return VoiceAsset
     */
    public function getAudioAsset()
    {
        return $this->audioAsset;
    }

    /**
     * @param VoiceAsset $audioAsset
     *
     * @return $this
     */
    public function setAudioAsset(VoiceAsset $audioAsset = null)
    {
        $this->setModelField('audioAsset', $audioAsset);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAllowExtension()
    {
        return $this->allowExtension;
    }

    /**
     * @param mixed $allowExtension
     *
     * @return $this
     */
    public function setAllowExtension($allowExtension)
    {
        $this->setModelField('allowExtension', $allowExtension);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAllowRepeatMenu()
    {
        return $this->allowRepeatMenu;
    }

    /**
     * @param mixed $allowRepeatMenu
     *
     * @return $this
     */
    public function setAllowRepeatMenu($allowRepeatMenu)
    {
        $this->setModelField('allowRepeatMenu', $allowRepeatMenu);

        return $this;
    }

    /**
     * @return VoiceAutoAttendantDialNumber[]|ArrayCollection
     */
    public function getDialNumbers()
    {
        return $this->dialNumbers;
    }

    /**
     * @param int $dialNum
     *
     * @return VoiceAutoAttendantDialNumber
     */
    public function getDialNumber($dialNum)
    {
        return $this->dialNumbers
            ->filter(function (VoiceAutoAttendantDialNumber $dialNumber) use ($dialNum) {
                return $dialNumber->getDialNum() === $dialNum;
            })
            ->first()
        ;
    }

    /**
     * @param VoiceAutoAttendantDialNumber $dialNumber
     *
     * @return $this
     */
    public function addDialNumber(VoiceAutoAttendantDialNumber $dialNumber)
    {
        $dialNumber->setVoiceAutoAttendant($this);
        $this->dialNumbers->add($dialNumber);

        return $this;
    }

    /**
     * @param VoiceAutoAttendantDialNumber $dialNumber
     *
     * @return $this
     */
    public function removeDialNumber(VoiceAutoAttendantDialNumber $dialNumber)
    {
        $this->dialNumbers->removeElement($dialNumber);

        return $this;
    }

    /**
     * @JMS\VirtualProperty()
     * @JMS\Type("array")
     *
     * @return array
     */
    public function getTargets()
    {
        $map = [];
        foreach (range(1, 9) as $dialNum) {
            $dialNumber    = $this->getDialNumber($dialNum);
            $map[$dialNum] = $dialNumber ? $dialNumber->getTarget() : null;
        }

        return $map;
    }
}

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

use DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\AbstractVoiceTarget;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class VoiceNumber.
 *
 * @ORM\Entity()
 * @ORM\Table(name="voice_numbers", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="number_sid", columns={"sid"})
 * })
 * @ORM\EntityListeners({"DeskPRO\Bundle\AppBundle\EventListener\Doctrine\Voice\VoiceNumberListener"})
 *
 * @JMS\ExclusionPolicy("all")
 *
 * @UniqueEntity("sid")
 */
class VoiceNumber implements EntityInterface, NotifyPropertyChanged
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
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\VoiceAccount", inversedBy="numbers")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<DeskPRO\Bundle\AppBundle\Entity\VoiceAccount>")
     *
     * @Assert\NotNull()
     *
     * @var VoiceAccount
     */
    private $account;

    /**
     * @ORM\Column(name="sid", type="string", length=50)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $sid;

    /**
     * @ORM\Column(name="number", type="string", length=255)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $number;

    /**
     * @ORM\Column(name="nickname", type="string", length=255, nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    private $nickname;

    /**
     * @ORM\Column(name="country_code", type="string", length=10)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     * @AppAssert\CountryCode()
     *
     * @var string
     */
    private $countryCode;

    /**
     * @ORM\OneToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\VoiceTarget\AbstractVoiceTarget", cascade={"persist"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="target_id", referencedColumnName="id", onDelete="SET NULL")
     *
     * @JMS\Expose()
     *
     * @Assert\Valid()
     * @Assert\NotNull()
     *
     * @var AbstractVoiceTarget
     */
    private $target;

    /**
     * @ORM\Column(name="outbound_calls_enabled", type="boolean")
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $outboundCallsEnabled = false;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return VoiceAccount
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param VoiceAccount $account
     *
     * @return $this
     */
    public function setAccount($account)
    {
        $this->setModelField('account', $account);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSid()
    {
        return $this->sid;
    }

    /**
     * @param mixed $sid
     *
     * @return $this
     */
    public function setSid($sid)
    {
        $this->setModelField('sid', $sid);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param mixed $number
     *
     * @return $this
     */
    public function setNumber($number)
    {
        $this->setModelField('number', $number);

        return $this;
    }

    /**
     * @return string
     */
    public function getNickname()
    {
        return $this->nickname;
    }

    /**
     * @param string $nickname
     *
     * @return $this
     */
    public function setNickname($nickname)
    {
        $this->setModelField('nickname', $nickname);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @param mixed $countryCode
     *
     * @return $this
     */
    public function setCountryCode($countryCode)
    {
        $this->setModelField('countryCode', $countryCode);

        return $this;
    }

    /**
     * @return AbstractVoiceTarget
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param AbstractVoiceTarget $target
     *
     * @return $this
     */
    public function setTarget(AbstractVoiceTarget $target = null)
    {
        $this->setModelField('target', $target);

        return $this;
    }

    /**
     * @return bool
     */
    public function isOutboundCallsEnabled()
    {
        return $this->outboundCallsEnabled;
    }

    /**
     * @param bool $outboundCallsEnabled
     *
     * @return $this
     */
    public function setOutboundCallsEnabled($outboundCallsEnabled)
    {
        $this->setModelField('outboundCallsEnabled', $outboundCallsEnabled);

        return $this;
    }
}

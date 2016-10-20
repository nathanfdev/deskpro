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

use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class VoiceAccount.
 *
 * @ORM\Entity()
 * @ORM\Table(name="voice_accounts", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="account_sid", columns={"account_sid"}
 * )})
 *
 * @JMS\ExclusionPolicy("all")
 *
 * @AppAssert\Voice\VoiceAccount()
 * @UniqueEntity("accountSid")
 */
class VoiceAccount implements EntityInterface, NotifyPropertyChanged
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
     * @ORM\Column(name="account_name", type="string", length=255)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $accountName;

    /**
     * @ORM\Column(name="account_sid", type="string", length=100)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $accountSid;

    /**
     * @ORM\Column(name="auth_token", type="string", length=100)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $authToken;

    /**
     * @ORM\Column(name="date_created", type="datetime")
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $dateCreated;

    /**
     * @ORM\OneToMany(targetEntity="VoiceNumber", mappedBy="account", cascade={"persist", "remove"})
     *
     * @var VoiceNumber[]
     */
    private $numbers;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->dateCreated = new \DateTime();
        $this->numbers     = new ArrayCollection();
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
    public function getAccountName()
    {
        return $this->accountName;
    }

    /**
     * @param string $accountName
     *
     * @return $this
     */
    public function setAccountName($accountName)
    {
        $this->setModelField('accountName', $accountName);

        return $this;
    }

    /**
     * @return string
     */
    public function getAccountSid()
    {
        return $this->accountSid;
    }

    /**
     * @param string $accountSid
     *
     * @return $this
     */
    public function setAccountSid($accountSid)
    {
        $this->setModelField('accountSid', $accountSid);

        return $this;
    }

    /**
     * @return string
     */
    public function getAuthToken()
    {
        return $this->authToken;
    }

    /**
     * @param string $authToken
     *
     * @return $this
     */
    public function setAuthToken($authToken)
    {
        $this->setModelField('authToken', $authToken);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @param \DateTime $dateCreated
     *
     * @return $this
     */
    public function setDateCreated($dateCreated)
    {
        $this->setModelField('dateCreated', $dateCreated);

        return $this;
    }
}

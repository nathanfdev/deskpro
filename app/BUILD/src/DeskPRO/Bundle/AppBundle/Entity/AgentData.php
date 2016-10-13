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

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AgentData.
 *
 * @ORM\Entity()
 * @ORM\Table(name="agent_data", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_extension_numbers", columns={"extension_number"})
 * })
 *
 * @JMS\ExclusionPolicy("all")
 *
 * @UniqueEntity(fields={"extensionNumber"}, errorPath="extensionNumber")
 */
class AgentData implements EntityInterface, NotifyPropertyChanged
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
     * @ORM\OneToOne(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     *
     * @var Person
     */
    private $person;

    /**
     * @ORM\Column(name="extension_number", type="integer", nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @AppAssert\Twilio\TwilioExtension()
     *
     * @var int
     */
    private $extensionNumber;

    /**
     * @ORM\OneToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\TwilioAsset", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="voicemail_asset_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @JMS\Expose()
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Entity\TwilioAsset")
     *
     * @Assert\Valid()
     *
     * @var TwilioAsset
     */
    private $voicemailAsset;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param Person $person
     *
     * @return $this
     */
    public function setPerson(Person $person = null)
    {
        $this->setModelField('person', $person);

        return $this;
    }

    /**
     * @return int
     */
    public function getExtensionNumber()
    {
        return $this->extensionNumber;
    }

    /**
     * @param int $extensionNumber
     *
     * @return $this
     */
    public function setExtensionNumber($extensionNumber)
    {
        $this->setModelField('extensionNumber', $extensionNumber);

        return $this;
    }

    /**
     * @return TwilioAsset
     */
    public function getVoicemailAsset()
    {
        return $this->voicemailAsset;
    }

    /**
     * @param TwilioAsset $voicemailAsset
     *
     * @return $this
     */
    public function setVoicemailAsset(TwilioAsset $voicemailAsset = null)
    {
        $this->setModelField('voicemailAsset', $voicemailAsset);

        return $this;
    }
}

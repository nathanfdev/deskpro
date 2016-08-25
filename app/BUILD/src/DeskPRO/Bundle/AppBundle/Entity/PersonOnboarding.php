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
use DeskPRO\Bundle\AppBundle\Validator\Constraints\CustomField\DateTime;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PersonOnboarding.
 *
 * @JMS\ExclusionPolicy("all")
 *
 * @ORM\Entity()
 * @ORM\Table("person_onboarding")
 */
class PersonOnboarding implements EntityInterface
{
    const STATUS_NEW         = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_COMPLETED   = 2;

    const APPLICATION_AGENT  = 'Agent';
    const APPLICATION_ADMIN  = 'Admin';
    const APPLICATION_PORTAL = 'Portal';

    /**
     * The unique log id.
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     * @JMS\Groups({"list"})
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", onDelete="CASCADE")
     * @Assert\Valid()
     *
     * @var Person
     */
    protected $person;

    /**
     * @JMS\Expose()
     *
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    protected $current_step = 0;

    /**
     * @JMS\Expose()
     *
     * @ORM\Column(type="string")
     *
     * @var string
     */
    protected $onboarding_class;

    /**
     * Message status (0 - new, 1 - in progree, 2 - completed).
     *
     * @JMS\Expose()
     *
     * @ORM\Column(type="integer", nullable=false)
     *
     * @Assert\NotNull()
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $status = self::STATUS_NEW;

    /**
     * @JMS\Expose()
     *
     * @ORM\Column(type="string")
     * 
     * @var string
     */
    protected $application;

    /**
     * @JMS\Expose()
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @var DateTime
     */
    protected $date_completion;

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
     * @return PersonOnboarding
     */
    public function setPerson($person)
    {
        $this->person = $person;

        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentStep()
    {
        return $this->current_step;
    }

    /**
     * @param int $current_step
     *
     * @return PersonOnboarding
     */
    public function setCurrentStep($current_step)
    {
        $this->current_step = $current_step;

        return $this;
    }

    /**
     * @return string
     */
    public function getOnboardingClass()
    {
        return $this->onboarding_class;
    }

    /**
     * @param string $onboarding_class
     *
     * @return PersonOnboarding
     */
    public function setOnboardingClass($onboarding_class)
    {
        $this->onboarding_class = $onboarding_class;

        return $this;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     *
     * @return PersonOnboarding
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @param mixed $application
     *
     * @return PersonOnboarding
     */
    public function setApplication($application)
    {
        $this->application = $application;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateCompletion()
    {
        return $this->date_completion;
    }

    /**
     * @param DateTime $date_completion
     *
     * @return PersonOnboarding
     */
    public function setDateCompletion($date_completion)
    {
        $this->date_completion = $date_completion;

        return $this;
    }
}

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
use Doctrine\Common\NotifyPropertyChanged;
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
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 */
class PersonOnboarding implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

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
     * @ORM\Column(type="integer", name="current_step")
     *
     * @var int
     */
    protected $currentStep = 0;

    /**
     * @JMS\Expose()
     *
     * @ORM\Column(type="string", name="onboarding_class")
     *
     * @var string
     */
    protected $onboardingClass;

    /**
     * Message status (0 - new, 1 - in progress, 2 - completed).
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
     * @ORM\Column(type="datetime", name="date_completion", nullable=true)
     *
     * @var DateTime
     */
    protected $dateCompletion;

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
    public function setPerson(Person $person)
    {
        $this->setModelField('person', $person);

        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentStep()
    {
        return $this->currentStep;
    }

    /**
     * @param int $currentStep
     *
     * @return PersonOnboarding
     */
    public function setCurrentStep($currentStep)
    {
        $this->setModelField('currentStep', $currentStep);

        return $this;
    }

    /**
     * @return string
     */
    public function getOnboardingClass()
    {
        return $this->onboardingClass;
    }

    /**
     * @param string $onboardingClass
     *
     * @return PersonOnboarding
     */
    public function setOnboardingClass($onboardingClass)
    {
        $this->setModelField('onboardingClass', $onboardingClass);

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
        $this->setModelField('status', $status);

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
        $this->setModelField('application', $application);

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateCompletion()
    {
        return $this->dateCompletion;
    }

    /**
     * @param DateTime $dateCompletion
     *
     * @return PersonOnboarding
     */
    public function setDateCompletion(DateTime $dateCompletion)
    {
        $this->setModelField('dateCompletion', $dateCompletion);

        return $this;
    }
}

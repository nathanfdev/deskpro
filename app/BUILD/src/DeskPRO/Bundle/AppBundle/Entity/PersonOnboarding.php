<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Person;
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
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person", inversedBy="onboarding")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @Assert\NotNull()
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
     * @var \DateTime
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
     * @return \DateTime
     */
    public function getDateCompletion()
    {
        return $this->dateCompletion;
    }

    /**
     * @param \DateTime $dateCompletion
     *
     * @return PersonOnboarding
     */
    public function setDateCompletion(\DateTime $dateCompletion = null)
    {
        $this->setModelField('dateCompletion', $dateCompletion);

        return $this;
    }
}

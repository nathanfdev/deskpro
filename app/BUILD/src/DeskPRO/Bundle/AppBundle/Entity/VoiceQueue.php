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
use Application\DeskPRO\Entity\Person;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class VoiceQueue.
 *
 * @ORM\Entity()
 * @ORM\Table(name="voice_queues")
 *
 * @JMS\ExclusionPolicy("all")
 */
class VoiceQueue implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    const ROUTING_MODEL_ROUND_ROBIN    = 'round_robin';
    const ROUTING_MODEL_LEAST_UTILIZED = 'least_utilized';
    const ROUTING_MODEL_LEAST_IDLE     = 'least_idle';
    const ROUTING_MODEL_RANDOM         = 'random';

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
     * @ORM\ManyToMany(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinTable(
     *      name="voice_queue_agents",
     *      joinColumns={
     *          @ORM\JoinColumn(name="voice_queue_id", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="person_id", referencedColumnName="id", onDelete="CASCADE")
     *      }
     * )
     *
     * @JMS\Expose()
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\Person>>")
     *
     * @var Person[]|ArrayCollection
     */
    private $agents;

    /**
     * @ORM\Column(name="routing_model", type="string", length=255)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $routingModel;

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
    private $greetAsset;

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
    private $loopAsset;

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
    private $voicemailAsset;

    /**
     * @ORM\Column(name="max_queue_size", type="integer")
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $maxQueueSize = 0;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->agents = new ArrayCollection();
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
     * @return mixed
     */
    public function getAgents()
    {
        return $this->agents;
    }

    /**
     * @param Person $person
     *
     * @return $this
     */
    public function addAgent(Person $person)
    {
        if ($this->agents->contains($person)) {
            $this->agents->add($person);
        }

        return $this;
    }

    /**
     * @param Person $person
     *
     * @return $this
     */
    public function removeAgent(Person $person)
    {
        $this->agents->removeElement($person);

        return $this;
    }

    /**
     * @return string
     */
    public function getRoutingModel()
    {
        return $this->routingModel;
    }

    /**
     * @param string $routingModel
     *
     * @return $this
     */
    public function setRoutingModel($routingModel)
    {
        $this->setModelField('routingModel', $routingModel);

        return $this;
    }

    /**
     * @return Blob
     */
    public function getGreetAsset()
    {
        return $this->greetAsset;
    }

    /**
     * @param Blob $greetAsset
     *
     * @return $this
     */
    public function setGreetAsset($greetAsset)
    {
        $this->setModelField('greetAsset', $greetAsset);

        return $this;
    }

    /**
     * @return Blob
     */
    public function getLoopAsset()
    {
        return $this->loopAsset;
    }

    /**
     * @param Blob $loopAsset
     *
     * @return $this
     */
    public function setLoopAsset($loopAsset)
    {
        $this->setModelField('loopAsset', $loopAsset);

        return $this;
    }

    /**
     * @return Blob
     */
    public function getVoicemailAsset()
    {
        return $this->voicemailAsset;
    }

    /**
     * @param Blob $voicemailAsset
     *
     * @return $this
     */
    public function setVoicemailAsset($voicemailAsset)
    {
        $this->setModelField('voicemailAsset', $voicemailAsset);

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxQueueSize()
    {
        return $this->maxQueueSize;
    }

    /**
     * @param int $maxQueueSize
     *
     * @return $this
     */
    public function setMaxQueueSize($maxQueueSize)
    {
        $this->setModelField('maxQueueSize', $maxQueueSize);

        return $this;
    }
}

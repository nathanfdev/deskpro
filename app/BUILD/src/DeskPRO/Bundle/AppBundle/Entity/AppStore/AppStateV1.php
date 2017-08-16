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

namespace DeskPRO\Bundle\AppBundle\Entity\AppStore;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Bundle\AppStoreBundle\Domain;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\AppStateRepository")
 * @ORM\Table(
 *  name="app2_app_state", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="state_unique", columns={"app_instance_id", "name", "owner_id"})
 *  })
 */
class AppStateV1 implements EntityInterface
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\AppStore\AppInstance")
     * @ORM\JoinColumn(name="app_instance_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var AppInstance
     */
    private $appInstance;

    /**
     * @ORM\Column(type="appstore_state_scope", nullable=false)
     *
     * @var Domain\StateScope
     */
    private $scope;

    /**
     * @ORM\Column(type="string", nullable=false)
     *
     * @return string
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=false)
     *
     * @return string
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity="\Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    private $owner;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $targetId;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return AppInstance
     */
    public function getAppInstance()
    {
        return $this->appInstance;
    }

    /**
     * @param AppInstance $appInstance
     *
     * @return $this
     */
    public function setAppInstance(AppInstance $appInstance = null)
    {
        $this->appInstance = $appInstance;

        return $this;
    }

    /**
     * todo BC, remove.
     *
     * {@inheritdoc}
     */
    public function getInstanceId()
    {
        return $this->appInstance ? $this->appInstance->getId() : null;
    }

    /**
     * @return Domain\StateScope
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param Domain\StateScope $scope
     *
     * @return $this
     */
    public function setScope(Domain\StateScope $scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * todo BC, remove.
     *
     * @return int
     */
    public function getOwnerId()
    {
        return $this->owner ? $this->owner->getId() : null;
    }

    /**
     * @param Person $person
     *
     * @return $this
     */
    public function setOwner(Person $person = null)
    {
        $this->owner = $person;

        return $this;
    }

    /**
     * @return \Application\DeskPRO\Entity\Person
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @return mixed
     */
    public function getTargetId()
    {
        return $this->targetId;
    }

    /**
     * @param mixed $targetId
     *
     * @return $this
     */
    public function setTargetId($targetId)
    {
        $this->targetId = $targetId;

        return $this;
    }
}

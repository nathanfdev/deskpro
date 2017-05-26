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

use DeskPRO\Bundle\AppStoreBundle\Domain;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *  name="app2_app_state", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="state_unique", columns={"app_instance_id", "name"})
 *  })
 */
class AppState implements Domain\ApplicationState
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
     * @todo this shouldnt be here, should be getId on $appInstance
     * @ORM\Column(name="app_instance_id", type="integer", nullable=false)
     *
     * @var int
     */
    private $appInstanceId;

    /**
     * @ORM\Column(type="appstore_state_scope", nullable=false)
     *
     * @var Domain\StateScope
     */
    private $scope;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=false)
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
     * @todo this shouldnt be here, shoul dbe getId on $owner
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ownerId;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $targetId;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @return AppInstance
     */
    public function getAppInstance()
    {
        return $this->appInstance;
    }

    /**
     * @param AppInstance $appInstance
     */
    public function setAppInstance(AppInstance $appInstance)
    {
        $this->appInstance   = $appInstance;
        $this->appInstanceId = null;
    }

    public function getInstanceId()
    {
        if (empty($this->appInstanceId) && !empty($this->appInstance)) {
            return $this->appInstance->getId();
        }

        return $this->appInstanceId;
    }

    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param Domain\StateScope $scope
     */
    public function setScope(Domain\StateScope $scope)
    {
        $this->scope = $scope;
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
     */
    public function setName($name)
    {
        $this->name = $name;
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
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return int
     */
    public function getOwnerId()
    {
        if (empty($this->ownerId) && !empty($this->owner)) {
            return $this->owner->getId();
        }

        return $this->ownerId;
    }

    /**
     * @param \Application\DeskPRO\Entity\Person $person
     */
    public function setOwner(\Application\DeskPRO\Entity\Person $person)
    {
        $this->owner   = $person;
        $this->ownerId = null;
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
     */
    public function setTargetId($targetId)
    {
        $this->targetId = $targetId;
    }
}

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
 *  }
    )
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
     * @ORM\JoinColumn(name="app_instance_id", referencedColumnName="id")
     *
     * @var AppInstance
     */
    private $appInstance;

    /**
     * @ORM\Column(name="app_instance_id", type="integer", nullable=false)
     * @var int
     */
    private $appInstanceId;

    /**
     * @ORM\Column(type="string", nullable=false)
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
    public function getAppInstance(): AppInstance
    {
        return $this->appInstance;
    }

    /**
     * @param AppInstance $appInstance
     */
    public function setAppInstance(AppInstance $appInstance)
    {
        $this->appInstance = $appInstance;
        $this->appInstanceId = null;
    }

    public function getInstanceId()
    {
        if (empty($this->appInstanceId) && !empty($this->appInstance)) {
            return $this->appInstance->getId();
        }

        return $this->appInstanceId;
    }

    /**false
     * @return mixed
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param mixed $scope
     */
    public function setScope($scope)
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
     * @return mixed
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    /**
     * @param mixed $ownerId
     */
    public function setOwnerId($ownerId)
    {
        $this->ownerId = $ownerId;
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

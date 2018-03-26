<?php

namespace DeskPRO\Bundle\AppBundle\Entity\AppStore;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\AppStateRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(
 *  name="app2_app_state_v2", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="state_unique", columns={"app_instance_id", "name", "entity_id", "person_id"})
 *  })
 */
class AppState implements EntityInterface
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
     * @ORM\Column(name="entity_id", type="string", nullable=false)
     *
     * @var string
     */
    private $entityId;

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
     * @ORM\Column(name="value_type", type="string", length=50, nullable=false)
     *
     * @return string
     */
    private $valueType = 'object';

    /**
     * @ORM\Column(name="perm_read", type="string", length=50, nullable=false)
     *
     * @return string
     */
    private $permRead = 'OWNER';

    /**
     * @ORM\Column(name="perm_write", type="string", length=50, nullable=false)
     *
     * @return string
     */
    private $permWrite = 'OWNER';

    /**
     * @ORM\Column(name="is_backend_only", type="boolean", nullable=false)
     *
     * @return bool
     */
    private $isBackendOnly = true;

    /**
     * @ORM\ManyToOne(targetEntity="\Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    private $owner;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $persistedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

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
     */
    public function setAppInstance(AppInstance $appInstance)
    {
        $this->appInstance = $appInstance;
    }

    /**
     * @return string
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @param string $entityId
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;
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
    public function getValueType()
    {
        return $this->valueType;
    }

    /**
     * @param mixed $valueType
     */
    public function setValueType($valueType)
    {
        $this->valueType = $valueType;
    }

    /**
     * @return mixed
     */
    public function getPermRead()
    {
        return $this->permRead;
    }

    /**
     * @param mixed $permRead
     */
    public function setPermRead($permRead)
    {
        $this->permRead = $permRead;
    }

    /**
     * @return mixed
     */
    public function getPermWrite()
    {
        return $this->permWrite;
    }

    /**
     * @param mixed $permWrite
     */
    public function setPermWrite($permWrite)
    {
        $this->permWrite = $permWrite;
    }

    /**
     * @return mixed
     */
    public function getIsBackendOnly()
    {
        return $this->isBackendOnly;
    }

    /**
     * @param mixed $isBackendOnly
     */
    public function setIsBackendOnly($isBackendOnly)
    {
        $this->isBackendOnly = $isBackendOnly;
    }

    /**
     * @return Person
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param Person $owner
     */
    public function setOwner(Person $owner)
    {
        $this->owner = $owner;
    }

    /**
     * @ORM\PrePersist
     */
    public function setTimestampsOnPersist()
    {
        $this->persistedAt = new \DateTime();
        $this->updatedAt   = new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function setTimestampsOnUpdate()
    {
        $this->updatedAt = new \DateTime();
    }
}

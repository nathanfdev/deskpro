<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\ApiKey;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="api_keys_actions")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 * @ORM\InheritanceType("NONE")
 */
class ApiKeyAction implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * @var int
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     * @Assert\NotNull()
     */
    protected $action;

    /**
     * @var ApiKey
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\ApiKey", inversedBy="actions")
     * @ORM\JoinColumn(name="api_key_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $key;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     *
     * @return $this
     */
    public function setAction($action)
    {
        $this->setModelField('action', $action);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param ApiKey $key
     *
     * @return $this
     */
    public function setKey(ApiKey $key)
    {
        $this->setModelField('key', $key);

        return $this;
    }
}

<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Entity;

use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;

/**
 * This is the entity that defines the schema for DeskPRO\Component\Lock\PdoStore.
 *
 * @ORM\Entity()
 * @ORM\Table(name="lock_keys")
 */
class LockKey implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * @var string
     *
     * @ORM\Id()
     * @ORM\Column(name="key_id", type="string", length=64, nullable=false)
     */
    protected $keyId;

    /**
     * @var string
     *
     * @ORM\Column(name="key_token", type="string", length=44, nullable=false)
     */
    protected $keyToken;

    /**
     * @var string
     *
     * @ORM\Column(name="key_expiration", type="integer", length=10)
     */
    protected $keyExpiration;

    /**
     * LockLey constructor.
     *
     * @param string $keyId
     * @param string $keyToken
     * @param string $keyExpiration
     */
    public function __construct($keyId, $keyToken, $keyExpiration)
    {
        $this->keyId         = $keyId;
        $this->keyToken      = $keyToken;
        $this->keyExpiration = $keyExpiration;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->keyId;
    }

    /**
     * @return string
     */
    public function getKeyId()
    {
        return $this->keyId;
    }

    /**
     * @return string
     */
    public function getKeyToken()
    {
        return $this->keyToken;
    }

    /**
     * @return string
     */
    public function getKeyExpiration()
    {
        return $this->keyExpiration;
    }

    /**
     * @param string $keyExpiration
     *
     * @return $this
     */
    public function setKeyExpiration($keyExpiration)
    {
        $this->setModelField('keyExpiration', $keyExpiration);

        return $this;
    }
}

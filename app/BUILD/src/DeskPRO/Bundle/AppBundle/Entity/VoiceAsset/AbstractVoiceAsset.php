<?php

namespace DeskPRO\Bundle\AppBundle\Entity\VoiceAsset;

use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Bundle\AppBundle\Entity\NotifyPropertyChangedTrait;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Orb\Util\Strings;

/**
 * Class VoiceAsset.
 *
 * @ORM\Entity()
 * @ORM\Table(name="voice_assets")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string", length=30)
 * @ORM\DiscriminatorMap({
 *     "text" = "VoiceTextAsset",
 *     "record" = "VoiceRecordAsset",
 *     "upload" = "VoiceUploadAsset"
 * })
 *
 * @JMS\ExclusionPolicy("all")
 */
abstract class AbstractVoiceAsset implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    const TYPE_TEXT   = 'text';
    const TYPE_RECORD = 'record';
    const TYPE_UPLOAD = 'upload';

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
    protected $id;

    /**
     * @ORM\Column(name="auth", type="string", length=20)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $auth;

    /**
     * @ORM\Column(name="date_created", type="datetime")
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $dateCreated;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->auth        = Strings::random(20);
        $this->dateCreated = new \DateTime();
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
    public function getAuth()
    {
        return $this->auth;
    }

    /**
     * @param string $auth
     *
     * @return $this
     */
    public function setAuth($auth)
    {
        $this->setModelField('auth', $auth);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @param \DateTime $dateCreated
     *
     * @return $this
     */
    public function setDateCreated(\DateTime $dateCreated = null)
    {
        $this->setModelField('dateCreated', $dateCreated);

        return $this;
    }

    /**
     * @return string
     */
    abstract public function getType();

    /**
     * @param string $type
     *
     * @return VoiceRecordAsset|VoiceTextAsset|VoiceUploadAsset
     */
    public static function createByType($type)
    {
        switch ($type) {
            case self::TYPE_UPLOAD:
                return new VoiceUploadAsset();
            case self::TYPE_RECORD:
                return new VoiceRecordAsset();
            case self::TYPE_TEXT:
                return new VoiceTextAsset();
            default:
                return;
        }
    }
}

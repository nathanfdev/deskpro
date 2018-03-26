<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace DeskPRO\Bundle\AppBundle\Entity;

use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="install_data")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 */
class InstallData implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * A build ID used to represent data that isnt specific
     * to a build version (e.g. just generic data).
     */
    const BUILD_DEFAULT = 'default';

    /**
     * @var string
     * @ORM\Id()
     * @ORM\Column(type="integer")
     */
    protected $build;

    /**
     * @var string
     * @ORM\Id()
     * @ORM\Column(type="string", length=75)
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(type="dpblob")
     */
    protected $data;

    /**
     * @param string $name
     * @param string $data
     *
     * @return InstallData
     */
    public static function createDefaultData($name, $data)
    {
        return new self(self::BUILD_DEFAULT, $name, $data);
    }

    /**
     * InstallData constructor.
     *
     * @param int    $build
     * @param string $name
     * @param string $data
     */
    public function __construct($build, $name, $data)
    {
        $this->build = $build;
        $this->name  = $name;
        $this->data  = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return [$this->build, $this->name];
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $data
     */
    public function setData($data)
    {
        if ($this->data !== $data) {
            $this->setModelField('data', $data);
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getBuild()
    {
        return $this->build;
    }
}

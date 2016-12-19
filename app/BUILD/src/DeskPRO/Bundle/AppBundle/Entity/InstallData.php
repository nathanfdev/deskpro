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
     * @ORM\Column(type="dpblob", length=-3)
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

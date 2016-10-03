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

use Application\DeskPRO\Entity\Brand;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="theme_sets")
 * @JMS\ExclusionPolicy("all")
 */
class ThemeSet implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     *
     * @var int
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="theme_id", type="string")
     * @Assert\NotNull()
     */
    protected $theme_id;

    /**
     * @var array
     * @ORM\Column(name="options", type="json_array")
     * @Assert\NotNull()
     */
    protected $options;

    /**
     * @var Brand
     * @ORM\OneToOne(targetEntity="Application\DeskPRO\Entity\Brand", mappedBy="theme_set")
     */
    protected $brand;

    /**
     * @var Brand
     * @ORM\OneToOne(targetEntity="Application\DeskPRO\Entity\Brand", mappedBy="edit_theme_set")
     */
    protected $brand2;

    /**
     * @ORM\OneToMany(targetEntity="DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage", mappedBy="chat", cascade={"persist", "remove"})
     *
     * @var ArrayCollection
     */
    protected $assets;

    public function __construct()
    {
        $this->setOptions([]);
        $this->assets = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getThemeId()
    {
        return $this->theme_id;
    }

    /**
     * @param string $theme_id
     */
    public function setThemeId($theme_id)
    {
        $this->setModelField('theme_id', $theme_id);
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->setModelField('options', $options);
    }

    public function getOption($name, $default = null)
    {
        return array_key_exists($name, $this->options) ? $this->options[$name] : $default;
    }

    public function setOption($name, $value)
    {
        $options = $this->options;

        if ($value !== null) {
            $options[$name] = $value;
        } else {
            unset($options[$name]);
        }

        $this->setModelField('options', $options);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return "#{$this->id}, theme_id: {$this->theme_id}, options: ".print_r($this->options);
    }

    /**
     * @return Brand
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param Brand $brand
     *
     * @return $this
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBrand2()
    {
        return $this->brand2;
    }

    /**
     * @param mixed $brand2
     *
     * @return $this
     */
    public function setBrand2($brand2)
    {
        $this->brand2 = $brand2;

        return $this;
    }
}

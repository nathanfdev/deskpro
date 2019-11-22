<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="theme_sets")
 * @JMS\ExclusionPolicy("all")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
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
     *
     * @ORM\Column(name="options", type="json_array")
     * @Assert\NotNull()
     */
    protected $options;

    /**
     * @ORM\OneToMany(targetEntity="DeskPRO\Bundle\AppBundle\Entity\ThemeSetAsset", mappedBy="theme_set", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @var ArrayCollection|ThemeSetAsset[]
     */
    protected $assets;

    /**
     * A theme_id that temporarily overrides getThemeId but is not persisted.
     * This is to support rendering old templates when new helpcenter theme is active.
     * See also \DeskPRO\Bundle\PortalBundle\Controller\Api\TicketController::newTicketAction.
     *
     * @var string
     */
    private $overrideThemeId;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->assets = new ArrayCollection();
        $this->setOptions([]);
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
        return $this->overrideThemeId ?: $this->theme_id;
    }

    /**
     * @param string $theme_id
     *
     * @return $this
     */
    public function setThemeId($theme_id)
    {
        $this->setModelField('theme_id', $theme_id);

        return $this;
    }

    /**
     * @param string $overrideThemeId
     *
     * @return ThemeSet
     */
    public function setOverrideThemeId($overrideThemeId)
    {
        $this->overrideThemeId = $overrideThemeId;

        return $this;
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
     *
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->setModelField('options', $options);

        return $this;
    }

    public function getOption($name, $default = null)
    {
        return array_key_exists($name, $this->options) ? $this->options[$name] : $default;
    }

    /**
     * @param $name
     * @param $value
     *
     * @return $this
     */
    public function setOption($name, $value)
    {
        $options = $this->options;

        if ($value !== null) {
            $options[$name] = $value;
        } else {
            unset($options[$name]);
        }

        $this->setModelField('options', $options);

        return $this;
    }

    /**
     * @return ThemeSetAsset[]|ArrayCollection
     */
    public function getAssets()
    {
        return $this->assets;
    }

    /**
     * @param ThemeSetAsset $asset
     *
     * @return $this
     */
    public function addAsset(ThemeSetAsset $asset)
    {
        $asset->setThemeSet($this);
        $this->assets->add($asset);

        return $this;
    }

    /**
     * @param ThemeSetAsset $asset
     *
     * @return $this
     */
    public function removeAsset(ThemeSetAsset $asset)
    {
        if ($this->assets->contains($asset)) {
            $asset->setThemeSet(null);
            $this->assets->removeElement($asset);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return "#{$this->id}, theme_id: {$this->theme_id}, options: ".print_r($this->options);
    }
}

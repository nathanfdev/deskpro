<?php

namespace DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Metadata;

use Metadata\MethodMetadata as BaseMethodMetadata;

/**
 * Class MethodMetadata.
 */
class MethodMetadata extends BaseMethodMetadata
{
    /**
     * @var array
     */
    protected $modes = [];

    /**
     * @var array
     */
    protected $tags = [];

    /**
     * @param $modes
     *
     * @return $this
     */
    public function setModes($modes)
    {
        $this->modes = $modes;

        return $this;
    }

    /**
     * @param $tags
     *
     * @return $this
     */
    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getModes()
    {
        return $this->modes;
    }

    /**
     * @return mixed
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize([
            $this->class,
            $this->name,
            $this->modes,
            $this->tags,
        ]);
    }

    /**
     * @param string $str
     */
    public function unserialize($str)
    {
        list($this->class, $this->name, $this->modes, $this->tags) = unserialize($str);
        $this->reflection                                          = new \ReflectionMethod($this->class, $this->name);
        $this->reflection->setAccessible(true);
    }
}

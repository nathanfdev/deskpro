<?php

namespace DeskPRO\Bundle\AppBundle\Annotation\Limits\Metadata;

use Metadata\MethodMetadata as BaseMethodMetadata;

/**
 * Class MethodMetadata.
 */
class MethodMetadata extends BaseMethodMetadata
{
    /**
     * @var bool
     */
    protected $limits_disabled = false;

    /**
     * @return $this
     */
    public function disableLimits()
    {
        $this->limits_disabled = true;

        return $this;
    }

    public function isLimitsDisabled()
    {
        return $this->limits_disabled;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize([
            $this->class,
            $this->name,
            $this->limits_disabled,
        ]);
    }

    /**
     * @param string $str
     */
    public function unserialize($str)
    {
        list($this->class, $this->name, $this->limits_disabled) = unserialize($str);
        $this->reflection                                       = new \ReflectionMethod($this->class, $this->name);
        $this->reflection->setAccessible(true);
    }
}

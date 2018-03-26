<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain\AppManifest;

use JMS\Serializer\Annotation as JMS;


class SettingValidator
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $type;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $pattern;

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType( $type )
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * @param string $pattern
     */
    public function setPattern( $pattern )
    {
        $this->pattern = $pattern;
    }
}

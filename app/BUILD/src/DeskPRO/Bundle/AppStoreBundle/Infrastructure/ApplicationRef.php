<?php

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure;

class ApplicationRef
{
    private $identifier;

    private $isName = false;

    /**
     * ApplicationRef constructor.
     *
     * @param string $identifier
     * @param bool   $isName
     */
    public function __construct($identifier, $isName)
    {
        $this->identifier = $identifier;
        $this->isName     = $isName;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return bool
     */
    public function isId()
    {
        return !$this->isName;
    }

    /**
     * @return bool
     */
    public function isName()
    {
        return $this->isName;
    }
}

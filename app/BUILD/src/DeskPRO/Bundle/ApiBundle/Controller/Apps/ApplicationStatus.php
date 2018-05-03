<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Apps;

use JMS\Serializer\Annotation as JMS;

class ApplicationStatus
{
    /**
     * @JMS\Type("boolean")
     *
     * @var boolean
     */
    private $isDev;

    /**
     * @JMS\Type("boolean")
     *
     * @var boolean
     */
    private $isInstalled;

    /**
     * @return bool
     */
    public function isIsDev()
    {
        return $this->isDev;
    }

    /**
     * @param bool $isDev
     */
    public function setIsDev( $isDev )
    {
        $this->isDev = $isDev;
    }

    /**
     * @return bool
     */
    public function isIsInstalled()
    {
        return $this->isInstalled;
    }

    /**
     * @param bool $isInstalled
     */
    public function setIsInstalled( $isInstalled )
    {
        $this->isInstalled = $isInstalled;
    }

}

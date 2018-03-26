<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Apps;

use JMS\Serializer\Annotation as JMS;

class PackageInstallDetails
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
     * @JMS\Type("string")
     *
     * @var string
     */
    private $homepage;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $title;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $iconUrl;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $author;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $description;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $readme;

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

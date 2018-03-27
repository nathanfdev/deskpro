<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use JMS\Serializer\Annotation as JMS;

class UserEmailBaseType extends EmailBaseType
{
    /**
     * Link to the portal.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $portalHome;

    /**
     * EmailBaseType constructor.
     *
     * @param string $portalHome
     */
    public function __construct($portalHome)
    {
        $this->portalHome = $portalHome;
    }
}

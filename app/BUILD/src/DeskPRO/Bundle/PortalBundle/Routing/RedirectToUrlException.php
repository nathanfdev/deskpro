<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Routing;

class RedirectToUrlException extends \InvalidArgumentException
{
    /**
     * @var string
     */
    private $url;

    public function __construct($url, $message = 'Redirect')
    {
        parent::__construct($message, 301);
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
}

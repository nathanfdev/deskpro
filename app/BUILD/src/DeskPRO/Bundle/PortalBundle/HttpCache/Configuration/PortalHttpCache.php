<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\HttpCache\Configuration;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;

abstract class PortalHttpCache implements ConfigurationInterface
{
    /**
     * @var string the name of the request attribute that has the content object (for etag/last modified)
     */
    public $content;

    /**
     * @return string|null
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string|null $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }
}

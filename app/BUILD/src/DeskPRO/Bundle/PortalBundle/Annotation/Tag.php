<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Tag
{
    public $name;
    public $default_options     = [];
    public $esi                 = false;
    public $allow_route_params  = false;
    public $always_guest_inline = true;
}

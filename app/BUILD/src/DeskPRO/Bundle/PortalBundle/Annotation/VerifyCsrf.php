<?php

namespace DeskPRO\Bundle\PortalBundle\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class VerifyCsrf
{
    public $name = '_dp_csrf_token';
}

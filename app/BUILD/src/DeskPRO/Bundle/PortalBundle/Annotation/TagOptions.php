<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class TagOptions
{
    public $defaults              = [];
    public $required              = [];
    public $allowed_values        = [];
    public $allowed_types         = [];
    public $attribute_expressions = [];

    /**
     * An array of variables to read from the request attributes
     * to merge into the current options.
     *
     * @var array
     */
    public $inherit_from = [];
}

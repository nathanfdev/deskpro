<?php

namespace DeskPRO\Bundle\ApiBundle\Util;

use FOS\RestBundle\Inflector\InflectorInterface;

/**
 * Inflector object using the Doctrine/Inflector.
 *
 * @author Mark Kazemier <Markkaz>
 */
class UrlInflector implements InflectorInterface
{
    /**
     * {@inheritdoc}
     */
    public function pluralize($word)
    {
        return $word;
    }
}

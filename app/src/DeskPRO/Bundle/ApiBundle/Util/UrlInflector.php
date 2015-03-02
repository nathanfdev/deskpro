<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace DeskPRO\Bundle\ApiBundle\Util;
use FOS\RestBundle\Util\Inflector\InflectorInterface;

/**
 * Inflector object using the Doctrine/Inflector
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

<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\Cache;

use Application\DeskPRO\Domain\DomainObject;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;

class EtagGenerator
{
    public function generate($parameters)
    {
        $segments = $this->flatten($this->createSegments($parameters));

        return $this->getHash($segments);
    }

    protected function getHash(array $segments)
    {
        return md5(implode('::', $segments));
    }

    protected function createSegments($params)
    {
        $segments = [];
        foreach ($params as $param) {
            switch (true) {
                case is_scalar($param):
                    $segment = $param;
                    break;
                case is_array($param) || $param instanceof \Traversable:
                    $segment = $this->createSegments($param);
                    break;
                case $param instanceof DomainObject || $param instanceof EntityInterface:
                    $segment = $param->getId();
                    break;
                default:
                    continue 2;

            }
            $segments[] = $segment;
        }

        return $segments;
    }

    protected function flatten($params)
    {
        $segments = [];
        array_walk_recursive($params, function ($a) use (&$segments) {$segments[] = $a;});

        return $segments;
    }
}

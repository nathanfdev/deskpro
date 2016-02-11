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
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;

/**
 * Class EtagGenerator.
 */
class EtagGenerator
{
    /**
     * @var SettingsResolver
     */
    protected $resolver;

    /**
     * @param SettingsResolver $resolver
     */
    public function __construct(SettingsResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * @param $parameters
     *
     * @return string
     */
    public function generate($parameters)
    {
        $segments = $this->createSegments($parameters);
        $segments = $this->flatten($segments);
        array_unshift($segments, $this->resolver->getGlobalSettings('api.cache.global_version'));

        return $this->getHash($segments);
    }

    /**
     * @param array $segments
     *
     * @return string
     */
    protected function getHash(array $segments)
    {
        return md5(implode('::', $segments));
    }

    /**
     * @param $params
     *
     * @return array
     */
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

    /**
     * @param $params
     *
     * @return array
     */
    protected function flatten($params)
    {
        $segments = [];
        array_walk_recursive($params, function ($a) use (&$segments) {$segments[] = $a;});

        return $segments;
    }
}

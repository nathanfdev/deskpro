<?php

namespace DeskPRO\Bundle\AppBundle\Cache;

use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Component\Util\TypeUtils;

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
        array_unshift($segments, $this->resolver->getGlobalSettings()->get('response.cache.global_version'));

        return $this->getHash($segments);
    }

    /**
     * @param array $segments
     *
     * @return string
     */
    protected function getHash(array $segments)
    {
        return sprintf('%s%s%s', '"', md5(implode('::', $segments)), '"');
    }

    /**
     * Well at last it lloks like proprietary buggy serialization.
     *
     * @param $params
     *
     * @return array
     */
    protected function createSegments($params)
    {
        $segments = [];
        foreach ($params as $param_name => $param) {
            switch (true) {
                case is_scalar($param):
                    $segment = $param;
                    break;
                case is_array($param) || $param instanceof \Traversable:
                    $segment = $this->createSegments($param);
                    break;
                case $param instanceof DomainObject || $param instanceof EntityInterface:
                    $segment = sprintf('%s(id=>%d)', TypeUtils::getSnakeCaseBaseTypeName($param), $param->getId());
                    break;
                case is_object($param):
                    $segment = $this->extractObject($param);
                    break;
                default:
                    continue 2;

            }
            if (is_int($param_name)) {
                $segments[] = $segment;
            } else {
                $segments[$param_name] = $segment;
            }
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
        array_walk_recursive(
            $params,
            function ($value, $key) use (&$segments) {
                $prefix = is_int($key) ? '' : "$key=>";
                $segments[] = sprintf('%s%s', $prefix, $value);
            }
        );

        return $segments;
    }

    /**
     * @param object $param
     *
     * @return mixed
     */
    protected function extractObject($param)
    {
        if (!is_object($param)) {
            throw new \InvalidArgumentException(sprintf('Passed argument should be and object but [ %s ] given', gettype($param)));
        }
        $name = TypeUtils::getSnakeCaseBaseTypeName($param);
        $vars = $this->createSegments(array_filter(get_object_vars($param), function ($value) {
            return is_scalar($value) && !is_null($value);
        }));

        return $segment = sprintf('%s(%s)', $name, implode(';', $this->flatten($vars)));
    }
}

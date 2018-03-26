<?php

namespace DeskPRO\Component\FilterQueryLanguage\Query\Opt;

use DeskPRO\Component\FilterQueryLanguage\Query\Query;
use DeskPRO\Component\FilterQueryLanguage\Query\QueryPart;

abstract class Opt extends QueryPart
{
    const OPT = Query::OPT_NONE;

    /**
     * @return array
     */
    abstract public function toArray();

    /**
     * @return string
     */
    public function getOptType()
    {
        return static::OPT;
    }

    /**
     * @param array $props
     *
     * @return Opt
     */
    public static function fromArray(array $props)
    {
        switch ($props['optType']) {
            case BetweenOpt::OPT:
                return BetweenOpt::fromArray($props);

            case CompareOpt::OPT:
                return CompareOpt::fromArray($props);

            case InOpt::OPT:
                return InOpt::fromArray($props);

            case NoOpt::OPT:
                return NoOpt::fromArray($props);

            default:
                throw new \InvalidArgumentException('Invalid optType');
        }
    }
}

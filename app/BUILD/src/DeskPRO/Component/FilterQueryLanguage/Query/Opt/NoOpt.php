<?php

namespace DeskPRO\Component\FilterQueryLanguage\Query\Opt;

use DeskPRO\Component\FilterQueryLanguage\Query\Query;

class NoOpt extends Opt
{
    const OPT = Query::OPT_NONE;

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'optType' => self::OPT,
        ];
    }

    /**
     * @param array $props
     *
     * @return NoOpt
     */
    public static function fromArray(array $props)
    {
        if ($props['optType'] !== self::OPT) {
            throw new \InvalidArgumentException('Expected optType of NONE_OPTION');
        }

        return new self();
    }
}

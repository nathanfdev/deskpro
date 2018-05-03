<?php

namespace DeskPRO\Component\FilterQueryLanguage\Query\Opt;

use DeskPRO\Component\FilterQueryLanguage\Query\Query;
use DeskPRO\Component\FilterQueryLanguage\Query\Val\Val;

class CompareOpt extends Opt
{
    const OPT = Query::OPT_COMPARE;

    /**
     * @var Val
     */
    public $value;

    /**
     * CompareOpt constructor.
     *
     * @param Val $value
     */
    public function __construct(Val $value)
    {
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'optType' => self::OPT,
            'value'   => $this->value->toArray(),
        ];
    }

    /**
     * @param array $props
     *
     * @return CompareOpt
     */
    public static function fromArray(array $props)
    {
        if ($props['optType'] !== self::OPT) {
            throw new \InvalidArgumentException('Expected optType of COMPARE_OPTION');
        }

        return new self(Val::fromArray($props['value']));
    }
}

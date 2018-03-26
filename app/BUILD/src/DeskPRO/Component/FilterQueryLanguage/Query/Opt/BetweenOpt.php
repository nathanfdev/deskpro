<?php

namespace DeskPRO\Component\FilterQueryLanguage\Query\Opt;

use DeskPRO\Component\FilterQueryLanguage\Query\Query;
use DeskPRO\Component\FilterQueryLanguage\Query\Val\Val;

class BetweenOpt extends Opt
{
    const OPT = Query::OPT_BETWEEN;

    /**
     * @var Val
     */
    public $value1;

    /**
     * @var Val
     */
    public $value2;

    /**
     * BetweenOpt constructor.
     *
     * @param Val $value1
     * @param Val $value2
     */
    public function __construct(Val $value1, Val $value2)
    {
        $this->value1 = $value1;
        $this->value2 = $value2;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'optType' => self::OPT,
            'value1'  => $this->value1->toArray(),
            'value2'  => $this->value2->toArray(),
        ];
    }

    /**
     * @param array $props
     *
     * @return BetweenOpt
     */
    public static function fromArray(array $props)
    {
        if ($props['optType'] !== self::OPT) {
            throw new \InvalidArgumentException('Expected optType of BETWEEN_OPTION');
        }

        return new self(
            Val::fromArray($props['value1']),
            Val::fromArray($props['value2'])
        );
    }
}

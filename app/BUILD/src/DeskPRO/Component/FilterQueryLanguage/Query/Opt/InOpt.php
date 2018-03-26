<?php

namespace DeskPRO\Component\FilterQueryLanguage\Query\Opt;

use DeskPRO\Component\FilterQueryLanguage\Query\Query;
use DeskPRO\Component\FilterQueryLanguage\Query\Val\Val;

class InOpt extends Opt
{
    const OPT = Query::OPT_IN;

    /**
     * @var Val[]
     */
    public $valueList = [];

    /**
     * InOpt constructor.
     *
     * @param Val[] $valueList
     */
    public function __construct(array $valueList)
    {
        $this->valueList = $valueList;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $vals = [];
        foreach ($this->valueList as $v) {
            $vals[] = $v->toArray();
        }

        return [
            'optType'   => self::OPT,
            'valueList' => $vals,
        ];
    }

    /**
     * @param array $props
     *
     * @return InOpt
     */
    public static function fromArray(array $props)
    {
        if ($props['optType'] !== self::OPT) {
            throw new \InvalidArgumentException('Expected optType of IN_OPTION');
        }

        $vals = [];
        foreach ($props['valueList'] as $v) {
            $vals[] = Val::fromArray($v);
        }

        return new self($vals);
    }
}

<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Component\FilterQueryLanguage\Query\Val;

use DeskPRO\Component\FilterQueryLanguage\Query\Query;

class FuncVal extends ComputedVal
{
    const VAL_TYPE = Query::VAL_FUNC;

    /**
     * @var string
     */
    public $name;

    /**
     * @var Val[]
     */
    public $params = [];

    /**
     * FuncVal constructor.
     *
     * @param string $name
     * @param Val[]  $params
     */
    public function __construct($name, array $params = [])
    {
        $this->name   = $name;
        $this->params = $params;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $params = [];
        foreach ($this->params as $p) {
            $params[] = $p->toArray();
        }

        return [
            'valueType' => self::VAL_TYPE,
            'name'      => $this->name,
            'params'    => $params,
            'tokenPos'  => $this->tokenPos,
        ];
    }

    /**
     * @param array $props
     *
     * @return FuncVal
     */
    public static function fromArray(array $props)
    {
        if ($props['valueType'] !== static::VAL_TYPE) {
            throw new \InvalidArgumentException(sprintf('Expected valueType of %s', static::VAL_TYPE));
        }

        $params = [];
        foreach ($props['params'] as $p) {
            $params[] = Val::fromArray($p);
        }

        $o = new self($props['name'], $params);
        if (!empty($props['tokenPos'])) {
            $o->tokenPos = $props['tokenPos'];
        }

        return $o;
    }
}

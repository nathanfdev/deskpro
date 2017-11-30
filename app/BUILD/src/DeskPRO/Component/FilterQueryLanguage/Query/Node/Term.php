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

namespace DeskPRO\Component\FilterQueryLanguage\Query\Node;

use DeskPRO\Component\FilterQueryLanguage\Query\Field;
use DeskPRO\Component\FilterQueryLanguage\Query\Op\Op;
use DeskPRO\Component\FilterQueryLanguage\Query\Opt\Opt;
use DeskPRO\Component\FilterQueryLanguage\Query\Query;

class Term extends Node
{
    const TYPE = Query::NODE_TERM;

    /**
     * @var Op
     */
    public $operator;

    /**
     * @var Field
     */
    public $field;

    /**
     * @var array|Opt
     */
    public $options;

    /**
     * @param Op    $operator
     * @param Field $field
     * @param Opt   $options
     */
    public function __construct(Op $operator, Field $field, Opt $options)
    {
        $this->operator = $operator;
        $this->field    = $field;
        $this->options  = $options;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'type'     => self::TYPE,
            'field'    => $this->field->toArray(),
            'operator' => $this->operator->getOperator(),
            'options'  => $this->options->toArray(),
            'tokenPos' => $this->tokenPos,
        ];
    }
}

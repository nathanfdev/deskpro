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

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms;

class FunctionCompareDef
{
    public $name;
    public $fields = [];
    public $matchFn;
    public $queryBuilderFn;
    public $operators;

    /**
     * @return FunctionCompareDef
     */
    public static function create()
    {
        return new self();
    }

    /**
     * @return string
     */
    public function getIdName()
    {
        return strtolower($this->name);
    }

    /**
     * @param mixed $name
     *
     * @return FunctionCompareDef
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param array $fields
     *
     * @return FunctionCompareDef
     */
    public function setFields($fields)
    {
        $this->fields = func_get_args();

        return $this;
    }

    /**
     * @param mixed $matchFn
     *
     * @return FunctionCompareDef
     */
    public function setMatchFn($matchFn)
    {
        $this->matchFn = $matchFn;

        return $this;
    }

    /**
     * @param mixed $queryBuilderFn
     *
     * @return FunctionCompareDef
     */
    public function setQueryBuilderFn($queryBuilderFn)
    {
        $this->queryBuilderFn = $queryBuilderFn;

        return $this;
    }

    /**
     * @param mixed $operators
     *
     * @return FunctionCompareDef
     */
    public function setOperators($operators)
    {
        $this->operators = func_get_args();

        return $this;
    }
}

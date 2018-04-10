<?php

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms;

use DeskPRO\Component\Util\ListUtils;

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
        $this->fields = ListUtils::flatten($this->fields);

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
        $this->operators = ListUtils::flatten($this->operators);

        return $this;
    }
}

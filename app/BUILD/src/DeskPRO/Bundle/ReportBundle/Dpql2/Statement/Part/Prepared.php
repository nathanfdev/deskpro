<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part;

/**
 * Represents a result of calling "prepare" on a DPQL part. This can define
 * things like the SQL returned, how it should be represented as a column
 * name, or the SQL expression if we're trying to print the result.
 */
class Prepared
{
    /**
     * The SQL expression this DPQL part prepared to.
     *
     * @var string
     */
    protected $_sqlExpr = 'NULL';

    /**
     * The name of the DPQL part for use in a column header.
     *
     * @var string
     */
    protected $_name = '';

    /**
     * The SQL expression that should be used if this DPQL part is in a printable context.
     *
     * @var string|bool
     */
    protected $_sqlExprPrint = false;

    /**
     * The SQL expression that should be used if this DPQL part is used in an ORDER BY.
     *
     * @var string|bool
     */
    protected $_sqlExprOrder = false;

    /**
     * A custom renderer that should be used to render this DPQL part.
     *
     * @var \Closure|string|null
     */
    protected $_renderer = null;

    /**
     * A callback to fill rows of the results based on values of this field (if grouping by it).
     *
     * @var \Closure|null
     */
    protected $_groupFill = null;

    /**
     * Controls whether a total is added for this value.
     *
     * @var bool
     */
    protected $_total = false;

    /**
     * Constructor.
     *
     * @param string               $sqlExpr      SQL expression
     * @param string               $name         Name of column header
     * @param string|bool          $sqlExprPrint SQL expression if in printable context
     * @param callable|string|null $renderer
     */
    public function __construct($sqlExpr = 'NULL', $name = '', $sqlExprPrint = false, $renderer = null)
    {
        $this->_sqlExpr      = $sqlExpr;
        $this->_name         = $name;
        $this->_sqlExprPrint = $sqlExprPrint;
        $this->_renderer     = $renderer;
    }

    /**
     * Determines if the SQL expression has a value (outputs something).
     *
     * @return bool
     */
    public function hasValue()
    {
        return $this->_sqlExpr !== '' && $this->_sqlExpr !== null && $this->_sqlExpr !== false;
    }

    /**
     * @param string $sql
     */
    public function setSql($sql)
    {
        $this->_sqlExpr = $sql;
    }

    /**
     * @return string
     */
    public function sql()
    {
        return $this->_sqlExpr;
    }

    /**
     * @param string|bool $printed
     */
    public function setPrinted($printed)
    {
        $this->_sqlExprPrint = $printed;
    }

    /**
     * Returns the printable SQL expression.
     *
     * @return string
     */
    public function printed()
    {
        return $this->_sqlExprPrint !== false ? $this->_sqlExprPrint : $this->_sqlExpr;
    }

    /**
     * @param string|bool $ordered
     */
    public function setOrdered($ordered)
    {
        $this->_sqlExprOrder = $ordered;
    }

    /**
     * Returns the orderable SQL expression. Returns the gen.
     *
     * @return string
     */
    public function ordered()
    {
        return $this->_sqlExprOrder !== false ? $this->_sqlExprOrder : $this->printed();
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->_name;
    }

    /**
     * @param \Closure|string|null $renderer
     */
    public function setRenderer($renderer = null)
    {
        $this->_renderer = $renderer;
    }

    /**
     * @return \Closure|string|null
     */
    public function renderer()
    {
        return $this->_renderer;
    }

    /**
     * @param \Closure|null $fill
     */
    public function setGroupFill(\Closure $fill = null)
    {
        $this->_groupFill = $fill;
    }

    /**
     * @return \Closure|null
     */
    public function groupFill()
    {
        return $this->_groupFill;
    }

    /**
     * @param bool $total
     */
    public function setTotal($total)
    {
        $this->_total = (bool) $total;
    }

    /**
     * @return bool
     */
    public function total()
    {
        return $this->_total;
    }
}

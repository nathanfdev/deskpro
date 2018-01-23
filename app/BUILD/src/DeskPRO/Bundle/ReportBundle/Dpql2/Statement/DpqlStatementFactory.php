<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Statement;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\AbstractPart;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Alias;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\AliasRef;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\BinaryComparison;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\BinaryInterval;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\BinaryLogical;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\BinaryMath;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Column;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\ColumnStar;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Exists;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\FunctionCall;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\In;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\InSubquery;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Like;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\NullValue;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Number;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\OrderDir;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Parentheses;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Placeholder;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Prepared;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Raw;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\RegExp;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\StringPart;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\SubSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\UnaryOperator;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Union;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Variable;

/**
 * Class StatementPartFactory.
 */
class DpqlStatementFactory
{
    /**
     * @var DeskproContainer
     */
    private $container;

    /**
     * Constructor.
     *
     * @param DeskproContainer $container
     */
    public function __construct(DeskproContainer $container)
    {
        $this->container = $container;
    }

    /**
     * @param array $parts
     *
     * @return Union
     */
    public function createUnion(array $parts)
    {
        return new Union($parts);
    }

    /**
     * @param array  $select
     * @param string $from
     *
     * @return SelectPart
     */
    public function createSelectPart(array $select, $from)
    {
        return new SelectPart(
            $this->container->get('doctrine.orm.default_entity_manager'),
            $this->container->getDbRead('reports'),
            $this->container->get('security.token_storage'),
            $this,
            $select,
            $from
        );
    }

    /**
     * @param SelectPart|AbstractPart $value
     * @param string                  $alias
     *
     * @return Alias
     */
    public function createAlias($value, $alias)
    {
        return new Alias($value, $alias);
    }

    /**
     * @param string $alias
     *
     * @return AliasRef
     */
    public function createAliasRef($alias)
    {
        return new AliasRef($alias);
    }

    /**
     * @param int          $operator
     * @param AbstractPart $lhs
     * @param AbstractPart $rhs
     *
     * @return BinaryComparison
     */
    public function createBinaryComparison($operator, AbstractPart $lhs, AbstractPart $rhs)
    {
        return new BinaryComparison($operator, $lhs, $rhs);
    }

    /**
     * @param int          $operator
     * @param AbstractPart $lhs
     * @param int          $amount
     * @param string       $unit
     *
     * @return BinaryInterval
     */
    public function createBinaryInterval($operator, AbstractPart $lhs, $amount, $unit)
    {
        return new BinaryInterval($operator, $lhs, $amount, $unit);
    }

    /**
     * @param int          $operator
     * @param AbstractPart $lhs
     * @param AbstractPart $rhs
     *
     * @return BinaryLogical
     */
    public function createBinaryLogical($operator, AbstractPart $lhs, AbstractPart $rhs)
    {
        return new BinaryLogical($operator, $lhs, $rhs);
    }

    /**
     * @param int          $operator
     * @param AbstractPart $lhs
     * @param AbstractPart $rhs
     *
     * @return BinaryMath
     */
    public function createBinaryMath($operator, AbstractPart $lhs, AbstractPart $rhs)
    {
        return new BinaryMath($operator, $lhs, $rhs);
    }

    /**
     * @param array $parts
     *
     * @return Column
     */
    public function createColumn(array $parts)
    {
        return new Column(
            $this,
            $this->container->get('dpql.func_registry'),
            $this->container->get('doctrine.orm.default_entity_manager'),
            $this->container->get('doctrine.dbal.default_connection'),
            $this->container->getTicketFieldManager(),
            $this->container->getBillingFieldManager(),
            $this->container->getPersonFieldManager(),
            $this->container->getOrgFieldManager(),
            $parts
        );
    }

    /**
     * @param array $parts
     *
     * @return ColumnStar
     */
    public function createColumnStar(array $parts)
    {
        return new ColumnStar(
            $this,
            $this->container->get('doctrine.orm.default_entity_manager'),
            $parts
        );
    }

    /**
     * @return Exists
     */
    public function createExists(SelectPart $subselect)
    {
        return new Exists($subselect);
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return FunctionCall
     */
    public function createFunctionCall($name, array $arguments = [])
    {
        return new FunctionCall(
            $this->container->get('dpql.func_registry'),
            $name,
            $arguments
        );
    }

    /**
     * @param AbstractPart   $lhs
     * @param AbstractPart[] $values
     * @param bool           $positive
     *
     * @return In
     */
    public function createIn(AbstractPart $lhs, array $values, $positive = true)
    {
        return new In($lhs, $values, $positive);
    }

    /**
     * @param AbstractPart $lhs
     * @param SelectPart   $subselect
     * @param bool         $positive
     *
     * @return InSubquery
     */
    public function createInSubquery(AbstractPart $lhs, SelectPart $subselect, $positive = true)
    {
        return new InSubquery($lhs, $subselect, $positive);
    }

    /**
     * @param AbstractPart $lhs
     * @param AbstractPart $rhs
     * @param bool         $positive
     *
     * @return Like
     */
    public function createLike(AbstractPart $lhs, AbstractPart $rhs, $positive = true)
    {
        return new Like($lhs, $rhs, $positive);
    }

    /**
     * @return NullValue
     */
    public function createNullValue()
    {
        return new NullValue();
    }

    /**
     * @param float|int $number
     *
     * @return Number
     */
    public function createNumber($number)
    {
        return new Number($number);
    }

    /**
     * @param AbstractPart $order
     * @param string       $orderDir
     *
     * @return OrderDir
     */
    public function createOrderDir(AbstractPart $order, $orderDir)
    {
        return new OrderDir($order, $orderDir);
    }

    /**
     * @param AbstractPart $expression
     *
     * @return Parentheses
     */
    public function createParentheses(AbstractPart $expression)
    {
        return new Parentheses($expression);
    }

    /**
     * @param string $name
     *
     * @return Placeholder
     */
    public function createPlaceholder($name)
    {
        return new Placeholder($this->container->get('dpql.placeholder_registry'), $name);
    }

    /**
     * @param string          $sqlExpr
     * @param string          $name
     * @param bool            $sqlExprPrint
     * @param string|callable $renderer
     *
     * @return Prepared
     */
    public function createPrepared($sqlExpr = 'NULL', $name = '', $sqlExprPrint = false, $renderer = null)
    {
        return new Prepared($sqlExpr, $name, $sqlExprPrint, $renderer);
    }

    /**
     * @param string $sql
     *
     * @return Raw
     */
    public function createRaw($sql)
    {
        return new Raw($sql);
    }

    /**
     * @param AbstractPart $lhs
     * @param AbstractPart $rhs
     * @param bool         $positive
     *
     * @return RegExp
     */
    public function createRegExp(AbstractPart $lhs, AbstractPart $rhs, $positive = true)
    {
        return new RegExp($lhs, $rhs, $positive);
    }

    /**
     * @param string $string
     *
     * @return StringPart
     */
    public function createStringPart($string)
    {
        return new StringPart($string);
    }

    /**
     * @param int          $operator
     * @param AbstractPart $value
     *
     * @return UnaryOperator
     */
    public function createUnaryOperator($operator, AbstractPart $value)
    {
        return new UnaryOperator($operator, $value);
    }

    /**
     * @param string $name
     *
     * @return Variable
     */
    public function createVariable($name)
    {
        return new Variable($name);
    }

    /**
     * @param string $sql
     *
     * @return SubSelect
     */
    public function createSubSelect($sql)
    {
        return new SubSelect($sql);
    }
}

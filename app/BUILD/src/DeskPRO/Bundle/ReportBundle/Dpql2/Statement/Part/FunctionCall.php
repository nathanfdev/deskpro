<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part;

use DeskPRO\Bundle\ReportBundle\Dpql2\Func\DpqlFuncRegistry;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Represents a call to a DPQL function.
 */
class FunctionCall extends AbstractPart
{
    /**
     * @var DpqlFuncRegistry
     */
    private $dpqlFuncRegistry;

    /**
     * @var string
     */
    public $name;

    /**
     * @var AbstractPart[]
     */
    public $arguments;

    /**
     * Constructor.
     *
     * @param DpqlFuncRegistry $dpqlFuncRegistry
     * @param string           $name
     * @param AbstractPart[]   $arguments
     */
    public function __construct(DpqlFuncRegistry $dpqlFuncRegistry, $name, array $arguments = [])
    {
        $this->dpqlFuncRegistry = $dpqlFuncRegistry;
        $this->name             = $name;
        $this->arguments        = $arguments;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        $childStack = $this->getChildStack($stack);
        $func       = $this->dpqlFuncRegistry->getFunction($this->name);

        return $func->prepare($this->arguments, $statement, $section, $childStack, $select, $metadata);
    }

    /**
     * {@inheritdoc}
     */
    public function toDpql(SelectPart $statement, $section, array $stack)
    {
        $arguments = [];
        foreach ($this->arguments as $argument) {
            $arguments[] = $argument->toDpql($statement, $section, $stack);
        }

        return $this->name.'('.implode(', ', $arguments).')';
    }
}

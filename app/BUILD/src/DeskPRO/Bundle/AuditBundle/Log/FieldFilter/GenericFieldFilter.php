<?php

namespace DeskPRO\Bundle\AuditBundle\Log\FieldFilter;

/**
 * Class GenericFieldFilter.
 */
class GenericFieldFilter implements FieldFilterInterface
{
    /**
     * @var callable
     */
    private $callable;

    /**
     * @var array
     */
    private $arguments;

    /**
     * GenericFieldFilter constructor.
     *
     * @param callable $callee
     * @param array    $arguments
     */
    public function __construct(callable $callee, array $arguments = [])
    {
        $this->callable  = $callee;
        $this->arguments = $arguments;
    }

    /**
     * {@inheritdoc}
     */
    public function filter($value, $argument = null)
    {
        $arguments = $this->arguments;
        array_unshift($arguments, $value);

        return call_user_func_array($this->callable, $arguments);
    }
}

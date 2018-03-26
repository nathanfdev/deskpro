<?php

namespace DeskPRO\Bundle\AuditBundle\Configuration;

use DeskPRO\Bundle\AuditBundle\Entity\NamingStrategy\NamingStrategyInterface;

/**
 * Class Configuration.
 */
class Configuration
{
    /**
     * @var string
     */
    private $entityClass;

    /**
     * @var string
     */
    private $action;

    /**
     * @var Condition[]
     */
    private $conditions = [];

    /**
     * @var array
     */
    private $fields = [];

    /**
     * @var bool
     */
    private $skipFields = false;

    /**
     * @var AuditContext
     */
    private $context;

    /**
     * @var NamingStrategyInterface
     */
    private $namingStrategy;

    /**
     * Configuration constructor.
     *
     * @param string $entityClass
     * @param string $action
     */
    public function __construct($entityClass, $action)
    {
        $this->entityClass = $entityClass;
        $this->action      = $action;
    }

    /**
     * @return string
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return array
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @param Condition $condition
     *
     * @return $this
     */
    public function addCondition(Condition $condition)
    {
        $this->conditions[spl_object_hash($condition)] = $condition;

        return $this;
    }

    /**
     * @return bool
     */
    public function shouldSkipFields()
    {
        return $this->skipFields;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param string $field
     *
     * @return $this;
     */
    public function addField($field)
    {
        $this->skipFields = true;
        $this->fields[]   = $field;

        return $this;
    }

    /**
     * @param AuditContext $context
     *
     * @return $this
     */
    public function setContext(AuditContext $context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @return bool
     */
    public function calculateConditions()
    {
        // Tremble, mortals, and despair! Bool has come to this world! (c) Lord Archimonde
        if (!$result = !(count($this->conditions) > 0)) {
            foreach ($this->conditions as $condition) {
                $result = $result || $condition->getBool($this->context);
                if ($result) {
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * @param NamingStrategyInterface $strategy
     *
     * @return $this
     */
    public function setNamingStrategy(NamingStrategyInterface $strategy)
    {
        $this->namingStrategy = $strategy;

        return $this;
    }

    /**
     * @return NamingStrategyInterface
     */
    public function getNamingStrategy()
    {
        return $this->namingStrategy;
    }
}

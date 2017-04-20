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

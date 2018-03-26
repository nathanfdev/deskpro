<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\CustomField;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Symfony\Component\Validator\Constraint;

/**
 * Class AbstractCustomDefConstraint.
 */
abstract class AbstractCustomDefConstraint extends Constraint
{
    /**
     * Could be "agent" or "user".
     *
     * @var string
     */
    public $context = 'agent';

    /**
     * @var CustomDefAbstract
     */
    public $custom_def;

    /**
     * @param string $name
     * @param bool   $use_prefix
     *
     * @return mixed
     */
    public function getCustomDefOption($name, $use_prefix = false)
    {
        if (!$this->custom_def) {
            throw new \InvalidArgumentException('Custom def is not defined');
        }

        $prefix = $use_prefix && $this->context === 'agent' ? 'agent_' : '';

        return $this->custom_def->getOption($prefix.$name);
    }
}

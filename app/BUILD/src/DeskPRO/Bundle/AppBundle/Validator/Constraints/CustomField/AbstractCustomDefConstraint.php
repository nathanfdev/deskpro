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
     * @var bool
     */
    public $check_required = true;

    /**
     * @param string $name
     * @param bool   $usePrefix
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    public function getCustomDefOption($name, $usePrefix = false)
    {
        if (!$this->custom_def) {
            throw new \InvalidArgumentException('Custom def is not defined');
        }

        $prefix = $usePrefix && $this->context === 'agent' ? 'agent_' : '';

        return $this->custom_def->getOption($prefix.$name);
    }
}

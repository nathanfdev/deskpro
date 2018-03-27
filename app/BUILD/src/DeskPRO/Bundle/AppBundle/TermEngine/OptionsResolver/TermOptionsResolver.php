<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\OptionsResolver;

use Symfony\Component\OptionsResolver\OptionsResolver;

class TermOptionsResolver extends OptionsResolver
{
    protected $constraints;

    public function setConstraints(array $constraints)
    {
        $this->constraints = [];

        foreach ($constraints as $option => $constraint_list) {
            $this->constraints[$option] = $constraint_list;
            $this->setDefined($option);
        }
    }

    public function getConstraints()
    {
        return $this->constraints ?: [];
    }
}

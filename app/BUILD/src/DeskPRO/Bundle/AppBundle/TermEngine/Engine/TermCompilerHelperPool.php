<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine;

class TermCompilerHelperPool
{
    /**
     * @var \DeskPRO\Bundle\AppBundle\TermEngine\TermCompilerHelperInterface[]
     */
    private $helpers;

    public function __construct(array $helpers)
    {
        /** @var \DeskPRO\Bundle\AppBundle\TermEngine\TermCompilerHelperInterface $helper */
        foreach ($helpers as $helper) {
            $this->helpers[$helper->getId()] = $helper;
        }
    }

    public function getHelper($id)
    {
        if (!array_key_exists($id, $this->helpers)) {
            throw new \InvalidArgumentException(sprintf('no helper with the id "%s" exists', $id));
        }

        return $this->helpers[$id];
    }
}

<?php

namespace DeskPRO\Bundle\PortalBundle\Twig;

abstract class Template extends \Twig_Template
{
    /**
     * {@inheritdoc}
     */
    public function getAttribute($object, $item, array $arguments = [], $type = self::ANY_CALL, $isDefinedTest = false, $ignoreStrictCheck = false)
    {
        return parent::getAttribute($object, $item, $arguments, $type, $isDefinedTest, $ignoreStrictCheck);
    }
}

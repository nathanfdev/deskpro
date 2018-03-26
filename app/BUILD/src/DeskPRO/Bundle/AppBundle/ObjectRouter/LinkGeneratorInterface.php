<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\ObjectRouter;

/**
 * Interface LinkGeneratorInterface.
 */
interface LinkGeneratorInterface
{
    /**
     * @param mixed  $object
     * @param string $type
     * @param string $context
     *
     * @return bool
     */
    public function supports($object, $type, $context);

    /**
     * @param mixed  $object
     * @param string $type
     * @param string $context
     * @param array  $extra_params
     * @param string $reference_type
     *
     * @return string
     */
    public function generate($object, $type, $context, $extra_params, $reference_type);
}

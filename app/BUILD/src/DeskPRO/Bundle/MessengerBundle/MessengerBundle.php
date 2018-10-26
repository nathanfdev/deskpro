<?php

namespace DeskPRO\Bundle\MessengerBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class MessengerBundle.
 */
class MessengerBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return __DIR__;
    }
}

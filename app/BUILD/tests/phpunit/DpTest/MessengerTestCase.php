<?php

namespace DpTest;

abstract class MessengerTestCase extends AbstractKernelAwareTestCase
{
    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        return $this->getMessengerKernel()->getContainer();
    }
}

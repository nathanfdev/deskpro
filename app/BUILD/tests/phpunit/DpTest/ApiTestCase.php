<?php

namespace DpTest;

abstract class ApiTestCase extends AbstractKernelAwareTestCase
{
    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        return $this->getApiKernel()->getContainer();
    }
}

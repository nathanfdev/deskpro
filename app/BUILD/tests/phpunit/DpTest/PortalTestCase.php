<?php

/**
 * DeskPRO.
 */

namespace DpTest;

class PortalTestCase extends AbstractKernelAwareTestCase
{
    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        return $this->getPortalKernel()->getContainer();
    }
}

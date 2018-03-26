<?php

/**
 * DeskPRO.
 */

namespace DpTest;

/**
 * Class PerformanceTestCase.
 */
class PerformanceTestCase extends AbstractKernelAwareTestCase
{
    /**
     * @var float
     */
    private $timer_started;

    /**
     * @var string
     */
    private $timer_target;

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        return $this->getPortalKernel()->getContainer();
    }

    /**
     * @param $timer_target
     */
    protected function startTimer($timer_target)
    {
        $this->timer_target  = $timer_target;
        $this->timer_started = microtime(true);
    }

    /**
     * @param float $seconds
     */
    protected function assertTimerHasNotExceeded($seconds)
    {
        $actual = microtime(true) - $this->timer_started;
        $status = $actual < $seconds ? 'OK' : 'Fail!';
        echo " | Performance: {$this->timer_target} took $actual sec [Limit: $seconds, $status]\n";
        $this->assertLessThan($seconds, $actual);
    }
}

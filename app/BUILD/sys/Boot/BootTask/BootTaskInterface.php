<?php

namespace DpSys\Boot\BootTask;

interface BootTaskInterface
{
    /**
     * @param \DpRun\DpEnv $env
     * @param array        $resources
     *
     * @return mixed
     */
    public function run(\DpRun\DpEnv $env, array $resources);
}

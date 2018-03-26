<?php

namespace Application\DeskPRO\JobQueue\Processor\Reset;

use Application\DeskPRO\App\AppManipulator;

class AppsProcessor extends Base
{
    const JOB_TYPE = 'reset.apps';

    /**
     * {@inheritdoc}
     */
    protected function doProcess(array $data)
    {
        /** @var AppManipulator $man */
        $man = $this->container->getSystemService('app_manipulator');
        foreach ($this->em->getRepository('DeskPRO:AppInstance')->findAll() as $app) {
            $man->uninstallInstance($app);
        }
    }
}

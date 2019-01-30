<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\CloudEmails;

use Application\EmailBundle\Queue\QueueRunner;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use FOS\RestBundle\Controller\Annotations as Rest;

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

/**
 * @package DeskPRO\Bundle\ApiBundle\Controller\CloudEmails
 * @ApiModes({"master_key", "key"})
 * @ApiUserContext("open")
 * @Rest\Route("/cloud-emails/outgoing-email/job")
 */
class OutgoingEmailController extends BaseController
{
    /**
     * @Rest\Post("")
     * @param $request
     * @return DTOOutgoingJob
     * @throws \Exception
     */
    public function changeStatus(Request $request)
    {
        /** @var QueueRunner $runner */
        $runner  = $this->getContainer()->get('email.queue_runner');

        $maxItems = 10;
        $timeLimit = 180;
        $runner->setLimits($maxItems, $timeLimit);
        $metrics = $runner->runQueue();

        $dto = new DTOOutgoingJob();
        $dto->setQueueRunMetrics($metrics);
        return $dto;
    }
}

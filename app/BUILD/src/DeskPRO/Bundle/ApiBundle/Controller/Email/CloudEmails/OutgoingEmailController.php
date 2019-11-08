<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\CloudEmails;

use Application\EmailBundle\Queue\QueueRunner;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * @ApiModes({"master_key", "key"})
 * @ApiUserContext("open")
 * @Rest\Route("/cloud-emails/outgoing-email/job")
 */
class OutgoingEmailController extends BaseController
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        if (!defined('DPC_IS_CLOUD')) {
            exit;
        }
    }

    /**
     * @Rest\Post("")
     *
     * @param $request
     *
     * @throws \Exception
     *
     * @return DTOOutgoingJob
     */
    public function changeStatus(Request $request)
    {
        /** @var QueueRunner $runner */
        $runner = $this->getContainer()->get('email.queue_runner');

        $maxItems  = 10;
        $timeLimit = 180;
        $runner->setLimits($maxItems, $timeLimit);
        $metrics = $runner->runQueue();

        $dto = new DTOOutgoingJob();
        $dto->setQueueRunMetrics($metrics);

        return $dto;
    }
}

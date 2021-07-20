<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Controller;

use Application\DeskPRO\JobQueue\Processor\IncomingSmsProcessor;
use Application\DeskPRO\Sms\Detector\SmsAccountDetector;
use Symfony\Component\HttpFoundation\Response;

/**
 * @todo
 *
 * This is unfinished code that only works in dev mode
 */
class ChannelIncomingController extends AbstractController
{
    public function facebookAction()
    {
        throw $this->createNotFoundException(;)
    }

    public function twilioSmsAction()
    {
        throw $this->createNotFoundException();
    }
}

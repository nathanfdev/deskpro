<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace Application\EmailBundle\Controller;

use Application\DeskPRO\Controller\AbstractController;
use Application\EmailBundle\EntityRepository\SendmailSourceStatusRepository;
use Application\EmailBundle\Event\Mail;
use Application\EmailBundle\Event\Subscriber\Sendgrid;
use deskpro_sendgrid\InstallerHandler as SendGridAppInstaller;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CallbackController extends AbstractController
{
    /** @var SendGrid service */
    protected $service;

    public function handleAction(Request $request)
    {
        /** @var EventDispatcher $ed */
        $ed       = $this->get('event_dispatcher');
        $response = new Response();

        if (!$data = json_decode($request->getContent(), 1)) {
            throw new BadRequestHttpException();
        }

        if ($this->container->getSetting(SendGridAppInstaller::NAME.'.enabled')) {
            /** @var SendmailSourceStatusRepository $rep */
            $rep = $this->em->getRepository('EmailBundle:SendmailSourceStatus');
            $ed->addSubscriber(new Sendgrid($rep));
        }

        foreach ($data as $entry) {
            if (!isset($entry['event'])) {
                throw new BadRequestHttpException();
            }

            $event = new Mail($entry);
            $ed->dispatch($entry['event'], $event);
        }

        return $response;
    }
}

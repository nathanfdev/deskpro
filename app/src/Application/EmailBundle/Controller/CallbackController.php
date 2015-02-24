<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\EmailBundle\Controller;

use Application\DeskPRO\Controller\AbstractController;
use Application\DeskPRO\HttpFoundation\Request;
use Application\EmailBundle\EntityRepository\SendmailSourceStatusRepository;
use Symfony\Component\HttpFoundation\Response;
use deskpro_sendgrid\InstallerHandler as SendGridAppInstaller;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CallbackController extends AbstractController
{
	/** @var SendGrid service */
	protected $service;

	public function handleAction(Request $request)
	{
		$response = new Response();

		if (!$this->container->getSetting(SendGridAppInstaller::NAME . '.enabled')) {
			throw new AccessDeniedHttpException;
		}

		if (!$data = json_decode($request->getContent(), 1)) {
			throw new BadRequestHttpException;
		}

		/** @var SendmailSourceStatusRepository $rep */
		$rep = $this->em->getRepository('EmailBundle:SendmailSourceStatus');

		foreach ($data as $entry) {

			if (!isset($entry['smtp-id']) || !isset($entry['email']) || !isset($entry['event'])) {
				throw new BadRequestHttpException;
			}

			$refparts = explode('@', $entry['smtp-id']);
			$ref = trim(@$refparts[0], '<>');
			$email = $entry['email'];
			$event = $entry['event'];
			$reason = @$entry['reason'] ?: 'ok';

			unset($entry['smtp-id'], $entry['email'], $entry['reason'], $entry['event']);

			$rep->queueStatus(
				$ref,
				$email,
				$event,
				$reason,
				json_encode($entry)
			);
		}

		$rep->flush();

		return $response;
	}
}
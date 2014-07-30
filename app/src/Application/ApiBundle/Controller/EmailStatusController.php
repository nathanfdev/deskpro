<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage ApiBundle
 */

namespace Application\ApiBundle\Controller;

use Application\ApiBundle\PermissionStrategy\UserTypePermission;
use Application\DeskPRO\Email\EmailSource\Finder as EmailSourceFinder;
use Application\DeskPRO\Email\EmailSource\FinderFilter as EmailSourceFinderFilter;
use Application\DeskPRO\Email\SendmailQueue\Finder as SendmailQueueFinder;
use Application\DeskPRO\Email\SendmailQueue\FinderFilter as SendmailQueueFinderFilter;
use Application\DeskPRO\EmailGateway\Runner;

class EmailStatusController extends AbstractController implements ProtectedControllerInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getPermissionStrategy()
	{
		return new UserTypePermission(UserTypePermission::ADMIN);
	}

	####################################################################################################################
	# get-email-sources
	####################################################################################################################

	public function listSourcesAction()
	{
		#------------------------------
		# Filter options
		#------------------------------

		$filter = new EmailSourceFinderFilter();
		$filter_input = $this->in->getArrayValue('filter');
		$form = $this->createFormBuilder($filter)
			->add('page', 'text')
			->add('statuses', 'choice', array(
				'choices'  => array_combine($filter->getValidStatuses(), $filter->getValidStatuses()),
				'required' => false,
				'multiple' => true
			))
			->add('date_start', 'date', array(
				'view_timezone' => $this->person->getTimezone(),
				'widget' => 'single_text',
				'input' => 'datetime',
				'required' => false
			))
			->add('date_end', 'date', array(
				'view_timezone' => $this->person->getTimezone(),
				'widget' => 'single_text',
				'input' => 'datetime',
				'required' => false
			))
			->add('subject', 'text', array(
				'required' => false
			))
			->add('account', 'text', array(
				'required' => false
			))
			->add('to', 'text', array(
				'required' => false
			))
			->add('from', 'text', array(
				'required' => false
			))
			->add('error_code', 'text', array(
				'required' => false
			))
			->getForm();

		$form->submit($filter_input);

		$finder = new EmailSourceFinder($this->em, $filter);

		$info    = $finder->getPageInfo();
		$results = $finder->getResults();

		return $this->createApiResponse(array(
			'page'          => $filter->getPage(),
			'num_pages'     => $info['num_pages'],
			'count'         => $info['count'],
			'email_sources' => $this->getApiData($results)
		));
	}


	####################################################################################################################
	# get-sendmail-queue
	####################################################################################################################

	public function listSendmailAction()
	{
		#------------------------------
		# Filter options
		#------------------------------

		$filter = new SendmailQueueFinderFilter();
		$filter_input = $this->in->getArrayValue('filter');
		$form = $this->createFormBuilder($filter)
			->add('page', 'text')
			->add('statuses', 'choice', array(
				'choices'  => array_combine($filter->getValidStatuses(), $filter->getValidStatuses()),
				'required' => false,
				'multiple' => true
			))
			->add('date_start', 'date', array(
				'view_timezone' => $this->person->getTimezone(),
				'widget' => 'single_text',
				'input' => 'datetime',
				'required' => false
			))
			->add('date_end', 'date', array(
				'view_timezone' => $this->person->getTimezone(),
				'widget' => 'single_text',
				'input' => 'datetime',
				'required' => false
			))
			->add('subject', 'text', array(
				'required' => false
			))
			->add('account', 'text', array(
				'required' => false
			))
			->add('to', 'text', array(
				'required' => false
			))
			->add('from', 'text', array(
				'required' => false
			))
			->add('error_code', 'text', array(
				'required' => false
			))
			->getForm();

		$form->submit($filter_input);

		$finder = new SendmailQueueFinder($this->em, $filter);

		$info    = $finder->getPageInfo();
		$results = $finder->getResults();

		$data = array();
		foreach ($results as $r) {
			$data[] = $r->toApiData(true, true);
		}

		return $this->createApiResponse(array(
			'page'           => $filter->getPage(),
			'num_pages'      => $info['num_pages'],
			'count'          => $info['count'],
			'sendmail_queue' => $data
		));
	}

	####################################################################################################################
	# get-source-info
	####################################################################################################################

	public function getSourceInfoAction($id)
	{
		$source = $this->em->find('DeskPRO:EmailSource', $id);
		if (!$source) {
			throw $this->createNotFoundException();
		}

		$info = array();

		$info['source'] = $this->getApiData($source);
		$info['source_log'] = null;

		if ($source->log_blob) {
			try {
				$info['source_log'] = $this->container->getBlobStorage()->copyBlobRecordToString($source->log_blob);
			} catch (\Exception $e) {
				$info['source_log'] = "Failed to read log file ({$e->getMessage()})";
			}
		}

		if ($this->in->getBool('with_raw') && $source->blob) {
			$info['source_raw'] = $this->container->getBlobStorage()->copyBlobRecordToString($source->blob);
		}

		$info['source_info'] = null;
		if ($source->source_info) {
			$info['source_info'] = $source->getSourceInfoAsString();
		}

		switch ($source->object_type) {
			case 'ticket':
				$t = $this->em->find('DeskPRO:Ticket', $source->object_id);
				if ($t) {
					$info['ticket'] = $t->toApiData();
				}
				break;

			case 'ticket_message':
				$m = $this->em->find('DeskPRO:TicketMessage', $source->object_id);
				if ($m) {
					$info['ticket_message'] = $m->toApiData();
					$info['ticket']         = $m->ticket ? $m->ticket->toApiData() : null;
				}
				break;
		}

		return $this->createApiResponse($info);
	}

	####################################################################################################################
	# delete-email-source
	####################################################################################################################

	public function deleteEmailSourceAction($id)
	{
		$source = $this->em->find('DeskPRO:EmailSource', $id);
		if (!$source) {
			throw $this->createNotFoundException();
		}

		if ($source->blob) {
			try {
				$this->container->getBlobStorage()->deleteBlobRecord($source->blob);
			} catch (\Exception $e) {}
		}

		if ($source->log_blob) {
			try {
				$this->container->getBlobStorage()->deleteBlobRecord($source->log_blob);
			} catch (\Exception $e) {}
		}

		$this->em->remove($source);
		$this->em->flush();

		return $this->createApiDeleteResponse();
	}

	####################################################################################################################
	# reprocess-email
	####################################################################################################################

	public function reprocessEmailSourceAction($id)
	{
		$source = $this->em->find('DeskPRO:EmailSource', $id);
		if (!$source) {
			throw $this->createNotFoundException();
		}

		$source['status'] = 'inserted';
		$source['error_code'] = null;

		$runner = new Runner();
		$runner->executeSource($source);

		return $this->createApiResponse(array(
			'status' => $source->status
		));
	}

	####################################################################################################################
	# get-sendmail-info
	####################################################################################################################

	public function getSendmailInfoAction($id)
	{
		$sendmail = $this->em->find('DeskPRO:SendmailQueue', $id);
		if (!$sendmail) {
			throw $this->createNotFoundException();
		}

		$info = array();

		$info['sendmail'] = $this->getApiData($sendmail);
		unset($info['sendmail']['log']);

		$info['sendmail_log'] = $sendmail->log;

		if ($this->in->getBool('with_raw') && $sendmail->blob) {
			$info['sendmail_raw'] = $sendmail->getMessageAsString();
		}

		return $this->createApiResponse($info);
	}

	####################################################################################################################
	# delete-sendmail-source
	####################################################################################################################

	public function deleteSendmailAction($id)
	{
		$sendmail = $this->em->find('DeskPRO:SendmailQueue', $id);
		if (!$sendmail) {
			throw $this->createNotFoundException();
		}

		if ($sendmail->blob) {
			try {
				$this->container->getBlobStorage()->deleteBlobRecord($sendmail->blob);
			} catch (\Exception $e) {}
		}

		$this->em->remove($sendmail);
		$this->em->flush();

		return $this->createApiDeleteResponse();
	}

	####################################################################################################################
	# resed-sendmail
	####################################################################################################################

	public function resendSendmailAction($id)
	{
		$sendmail = $this->em->find('DeskPRO:SendmailQueue', $id);
		if (!$sendmail) {
			throw $this->createNotFoundException();
		}

		$sendmail['status'] = 'pending';
		$sendmail['date_next_attempt'] = new \DateTime();

		$this->em->persist($sendmail);
		$this->em->flush();

		return $this->createApiResponse(array(
			'date_next_attempt' => $sendmail->date_next_attempt
		));
	}
}
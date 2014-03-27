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

		return $this->createApiResponse(array(
			'page'           => $filter->getPage(),
			'num_pages'      => $info['num_pages'],
			'count'          => $info['count'],
			'sendmail_queue' => $this->getApiData($results)
		));
	}
}
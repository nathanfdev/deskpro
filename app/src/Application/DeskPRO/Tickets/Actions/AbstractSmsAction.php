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
 * @category Twilio
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\AppInstance;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\SnippetFormatter;
use Orb\Util\Util;

abstract class AbstractSmsAction extends AbstractContainerAwareAction implements ActionInterface, AppActionInterface
{
	/**
	 * All children of this class need to construct their own provider from their config
	 *
	 * @return \Orb\Sms\SmsProviderInterface
	 */
	public abstract function getSmsProvider();

	/**
	 * If your provider needs a "from" address to work, return a string. Otherwise, it's ok to return null.
	 *
	 * @return string|null
	 */
	public abstract function getFromPhoneNumber();

	/**
	 * @var
	 */
	protected $app;

	/**
	 * @return AppInstance
	 */
	protected function getApp()
	{
		if ($this->app !== null) {
			return $this->app;
		}

		$this->app = false;
		$app_manager = $this->getContainer()->getAppManager();
		$app_id = $this->getMetaData()->get('app_id', 0);

		if ($app_manager->hasApp($app_id)) {
			$this->app = $app_manager->getApp($app_id);
		}

		return $this->app === false ? null : $this->app;
	}


	/**
	 * {@inheritDoc}
	 */
	public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
	{
		if (!$this->getApp()) {
			$context->getLogger()->debug(
				sprintf('[%s] No app (app id: %d)', $this->getActionType(), $this->getMetaData()->get('app_id'))
			);
		}

		// configure our SMS sender
		$sms_sender = $this->getContainer()->get('deskpro.sms_sender');
		$sms_sender->setDefaultProvider($this->getSmsProvider());
		$sms_sender->setDefaultFromNumber($this->getFromPhoneNumber());

		// get the message (replace the twig vars)
		$action_message_template = $this->getActionOption('message');
		$formatter = new SnippetFormatter($this->getContainer()->getTwig());
		$message = $formatter->formatText($action_message_template, $ticket);

		$plain_to_numbers = array();
		$plain_to_numbers[] = $this->getActionOption('to_number');

		foreach ($plain_to_numbers as $number) {
			$extra_info = 'phone number';
			$this->logSendingTo($number, $context);
			try {
				$result = $sms_sender->send($number, $message);
				// plan is to add these types of methods on DeskPROSmsSender...
				//$sms_sender->sendToAgent($agent, $message);
				//$sms_sender->sendToAgents($agents, $message);

				$this->recordTicketStateChange($ticket, $number, $extra_info);

				if ($result->isFail()) {
					$this->logErrorSendingTo($result->getProviderMessage(), $context);
				}
			} catch (\Exception $e) {
				$this->logErrorSendingTo($e->getMessage(), $context);
			}
		}
	}

	/**
	 * @param                          $to_number
	 * @param ExecutorContextInterface $context
	 */
	protected function logSendingTo($to_number, ExecutorContextInterface $context)
	{
		$context->getLogger()->debug(
			sprintf(
				'[%s] Sending SMS message from "%s" to "%s"',
				$this->getActionType(),
				$this->getFromPhoneNumber(),
				$to_number
			)
		);
	}

	/**
	 * @param                          $e
	 * @param ExecutorContextInterface $context
	 */
	protected function logErrorSendingTo($message, ExecutorContextInterface $context)
	{
		$context->getLogger()->notice("[{$this->getActionType()}] Error sending SMS message: {$message}");
	}

	/**
	 * @param Ticket $ticket
	 * @param string $to_number
	 * @param string $to_extra_info - an info other than the to number that should be present in the record
	 */
	protected function recordTicketStateChange(Ticket $ticket, $to_number, $to_extra_info)
	{
		$app = $this->getApp();

		$recordMsg = sprintf('Sent SMS message to %s (%s)', $to_extra_info, $to_number);

		$ticket->getStateChangeRecorder()->recordData(
			'app_message',
			array(
				'app_id'        => $app->id,
				'app_title'     => $app->title,
				'package_name'  => $app->package->name,
				'package_title' => $app->package->title,
				'message'       => $recordMsg
			)
		);
	}

	/**
	 * @return string
	 */
	public function getActionType()
	{
		return Util::getBaseClassname($this).$this->getMetaData()->get('app_id', 0);
	}
}

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

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use DeskPRO\Kernel\KernelErrorHandler;
use Guzzle\Http\Client as HttpClient;
use Orb\Util\CheckedOptionsArray;
use Orb\Util\Strings;

/**
 * Execute a web hook
 *
 * @option string url
 * @option string username
 * @option string password
 * @option string method
 * @option string custom_data
 * @option string headers
 * @option int    timeout
 */
class WebHook extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface
{
	/**
	 * {@inheritDoc}
	 */
	protected function getOptionsDef()
	{
		$options = new CheckedOptionsArray();
		$options->addRequiredNames('url');
		$options->addValidNames('username', 'password', 'method', 'custom_data', 'headers', 'timeout', 'payload_type');
		return $options;
	}


	/**
	 * {@inheritDoc}
	 */
	public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
	{
		$timeout = intval($this->getActionOption('timeout')) ?: 20;

        /** @var TemplatingExtension $renderer */
        $renderer = $this->getContainer()->getTwig()->getExtension('deskpro_templating');
        $url = $renderer->renderTicketTemplate($this->getActionOption('url'), $ticket, $context);
        $headers = $renderer->renderTicketTemplate($this->getActionOption('headers'), $ticket, $context);
        $custom_data = $renderer->renderTicketTemplate($this->getActionOption('custom_data') ?: '', $ticket, $context);
        $username = $renderer->renderTicketTemplate($this->getActionOption('username') ?: '', $ticket, $context);
        $password = $renderer->renderTicketTemplate($this->getActionOption('password') ?: '', $ticket, $context);

        $http_client = new HttpClient($url, array(
            'timeout' => $timeout
        ));

		if ($headers) {
			$headers = Strings::parseEqualsLines($headers, Strings::EQUALSLINES_DUPE_ADD_ARRAY, ':');
		} else {
			$headers = array();
		}

		$data = array();
		$data['ticket']          = $ticket->toApiData();
		$data['person_context']  = $context->getPersonContext()->toApiData();
		$data['event_performer'] = $context->getEventPerformer();
		$data['event_type']      = $context->getEventType();
		$data['event_method']    = $context->getEventMethod();
		$data['custom_data']     = $custom_data;

		if ('json' === $this->getActionOption('payload_type')) {
			$headers['content-type'] = 'application/json';
		}

		$request = $http_client->createRequest($this->getActionOption('method') ?: 'POST', null, $headers, $data);

		if ($username || $password) {
			$request->setAuth($username ?: '', $password ?: '');
		}

		try {
			$request->send();
		} catch (\Exception $e) {
			KernelErrorHandler::logException($e, false, 'webhook_' . md5($this->getActionOption('url')));
		}
	}


	/**
	 * {@inheritDoc}
	 */
	public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
	{
		return null;
	}


	/**
	 * {@inheritDoc}
	 */
	public function applyMacro(Person $person, Ticket $ticket, ExecutorContextInterface $context)
	{
		$this->applyAction($ticket, $context);
	}
}
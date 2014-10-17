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
 * @subpackage ApiBundle
 */

namespace Application\ApiBundle\Controller;

use DeskPRO\Kernel\KernelErrorHandler;

class SaveLogController extends AbstractController
{
	public function logJsErrorAction()
	{
		$message     = $this->in->getString('message');
		$script_file = $this->in->getString('script_file');
		$script_line = $this->in->getUint('script_line');
		$trace       = $this->in->getString('trace');
		$context     = $this->in->getArrayValue('context');

		$url = '';
		if (isset($context['url'])) {
			$url = $context['url'];
			unset($context['url']);
		}

		$einfo = array(
			'type'          => 'error',
			'pri'           => 'NOTICE',
			'summary'       => $message,
			'errname'       => 'JSError',
			'errfile'       => $script_file,
			'errline'       => $script_line,
			'errstr'        => $message,
			'time_to_error' => '0',
			'url'           => $url,
			'client_user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
			'context_data'  => print_r($context, true),
			'trace'         => $trace,
			'session_name'  => DP_REQUEST_ID,
			'die'           => false,
			'display'       => false,
			'process_log'   => ''
		);

		@KernelErrorHandler::logErrorInfo($einfo);

		return $this->createSuccessResponse();
	}
}
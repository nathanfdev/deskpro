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
*/

namespace Application\DeskPRO\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Util;
use Orb\Util\Arrays;

class DataController extends AbstractController
{
	public function interfaceDataAction()
	{
		$what = $this->in->getCleanValueArray('types', 'string', 'discard');

		$js = array();


		$js = implode("\n", $js);
		$response = App::getResponse();
		$response->headers->set('Content-Type', 'application/javascript');
		$response->setContent($js);

		return $response;
	}

	public function agentLangJsAction()
	{
		$tr = $this->container->getTranslator();

		$js_phrases = array();
		$js_phrases['agent.general.add_a_label'] = $tr->getPhraseText('agent.general.add_a_label');
		$js_phrases['agent.general.check_on']    = $tr->getPhraseText('agent.general.check_on');
		$js_phrases['agent.general.check_off']   = $tr->getPhraseText('agent.general.check_off');

		$js_phrases["agent.general.reltime_less_second"]    = $tr->getPhraseText("agent.general.reltime_less_second");
		$js_phrases["agent.general.reltimeago_less_second"] = $tr->getPhraseText("agent.general.reltime_less_second");

		foreach (array('reltime', 'reltimeago') as $pre) {
			foreach (array('second', 'minute', 'hour', 'day', 'week', 'month', 'year') as $name) {
				$js_phrases["agent.general.{$pre}_1_{$name}"] = $tr->getPhraseText("agent.general.{$pre}_1_{$name}");
				$js_phrases["agent.general.{$pre}_x_{$name}"] = $tr->getPhraseText("agent.general.{$pre}_x_{$name}");
			}
		}

		$add_phrases = array(
			'agent.userchat.message_started',
			'agent.userchat.message_user_joined',
			'agent.userchat.message_user_left',
			'agent.userchat.message_set_department',
			'agent.userchat.assigned_to',
			'agent.userchat.msg_new_user_track',
			'agent.userchat.unassigned',
			'agent.userchat.msg_agent_timeout',
			'agent.userchat.msg_user_timeout',
			'agent.userchat.ended_by',
			'agent.userchat.ended',
			'agent.userchat.ended_user',
		);

		foreach ($add_phrases as $k) {
			$js_phrases[$k] = $tr->getPhraseText($k);
		}

		$js = "window.DESKPRO_LANG = " . json_encode($js_phrases) . ";";

		$response = $this->response;
		\Application\DeskPRO\HttpFoundation\ResponseUtil::setNeverExpireHeaders($response);
		$response->headers->set('Content-Type', 'application/javascript');
		$response->setContent($js);

		return $response;
	}

	public function userLangJsAction()
	{
		$tr = $this->container->getTranslator();

		$js_phrases = array();

		$js_phrases["user.general.reltime_less_second"] = $tr->phrase("user.general.reltime_less_second");
		$js_phrases["user.general.reltimeago_less_second"] = $tr->phrase("user.general.reltime_less_second");

		foreach (array('reltime', 'reltimeago') as $pre) {
			foreach (array('second', 'minute', 'hour', 'day', 'week', 'month', 'year') as $name) {
				$js_phrases["user.general.{$pre}_1_{$name}"] = $tr->phrase("user.general.{$pre}_1_{$name}");
				$js_phrases["user.general.{$pre}_x_{$name}"] = $tr->phrase("user.general.{$pre}_x_{$name}");
			}
		}

		$add_phrases = array(
			'user.chat.message_started',
			'user.chat.message_user_joined',
			'user.chat.message_user_left',
			'user.chat.message_set_department',
			'user.chat.assigned_to',
			'user.chat.unassigned',
			'user.chat.msg_agent_timeout',
			'user.chat.msg_user_timeout',
			'user.chat.ended_by',
			'user.chat.ended',
			'user.chat.ended_user',
		);

		foreach ($add_phrases as $k) {
			$js_phrases[$k] = $tr->getPhraseText($k);
		}

		$js = "window.DESKPRO_LANG = " . json_encode($js_phrases) . ";";

		$response = $this->response;
		\Application\DeskPRO\HttpFoundation\ResponseUtil::setNeverExpireHeaders($response);
		$response->headers->set('Content-Type', 'application/javascript');
		$response->setContent($js);

		return $response;
	}

	public function logJsErrorAction()
	{
		// The incoming data is sent from ErrorLogger.js

		$info = array(
			'message'           => $this->in->getString('message'),
			'trace'             => $this->in->getString('trace'),
			'script'            => $this->in->getString('script'),
			'line'              => $this->in->getString('line'),
			'client_user_agent' => isset($_SERVER['HTTP_REFERER'])    ? $_SERVER['HTTP_REFERER'] : '',
			'client_request'    => isset($_REQUEST)                   ? implode(', ', array_keys($_REQUEST)) : '',
			'client_fragment'   => $this->in->getString('fragment'),
		);

		\Application\DeskPRO\Service\ErrorReporter::reportJsError($info);

		return $this->createJsonResponse(array(
			'logged' => true
		));
	}

	public function sendErrorReportAction()
	{
		$error_text = $this->in->getString('error_text');

		$ip_address = $this->request->getClientIp();
		$user_agent = empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'];
		$referrer   = empty($_SERVER['HTTP_REFERER']) ? '' : $_SERVER['HTTP_REFERER'];
		$hash       = $this->in->getString('hash');

		$info = array(
			'hash' => $this->in->getString('hash'),
			'ip_address' => $ip_address,
			'user_agent' => $user_agent,
			'referrer' => $referrer,
			'fragment' => $hash,
			'comment' => $this->in->getString('comment'),
			'error_text' => $error_text,
		);

		\Application\DeskPRO\Service\ErrorReporter::sendReport('report-error-manual', array('error_summary' => 'Manually submitted error report', 'log' => $info), 10);

		return $this->createJsonResponse(array('success' => true));
	}
}

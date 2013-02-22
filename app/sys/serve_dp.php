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

namespace DeskPRO\Kernel;

if (!defined('DP_ROOT')) exit('No access');

require_once DP_ROOT.'/src/Orb/Util/Strings.php';
require_once DP_ROOT.'/src/Orb/Util/Util.php';
require_once DP_ROOT.'/sys/serve_abstract.php';

use Orb\Util\Util;

/**
 * A light-weight loader for website widgetss
 */
class DpLoader extends LoaderAbstract
{
	public function runAction()
	{
		try {
			$pathinfo = $this->getPathInfo();

			if (preg_match('#^/chat/is-available\.js#', $pathinfo)) {
				$this->isChatAvailableAction();

			} elseif (preg_match('#^/request-session\.(json|js)#', $pathinfo)) {
				$this->requestSessionAction();

			} elseif (preg_match('#^/session-ping\.json#', $pathinfo)) {
				$this->sessionPingAction();

			} elseif (preg_match('#^/user-lang-(\d+)\.js#', $pathinfo, $match)) {
				$this->userLanguageAction($match[1]);

			} elseif (preg_match('#^/agent-lang-(\d+)\.js#', $pathinfo, $match)) {
				$this->agentLanguageAction($match[1]);

			} else {
				header("HTTP/1.0 404 Not Found");
				echo "Action not found. (1)";
			}
		} catch (\Exception $exception) {
			if (isset($DP_CONFIG['debug']['dev'])) {
				echo "\n\n[{$exception->getCode()}] {$exception->getMessage()}\n\n";

				$backtrace = $exception->getTrace();
				$trace = self::formatBacktrace($backtrace);
				echo $trace;
			}

			$this->handleException($exception);
		}
	}

	####################################################################################################################
	# sessionPing
	####################################################################################################################

	protected function sessionPingAction()
	{
		$sids = array();
		$sids['u']  = !empty($_COOKIE['dpsid'])       ? $_COOKIE['dpsid']       : '';
		$sids['a']  = !empty($_COOKIE['dpsid-agent']) ? $_COOKIE['dpsid-agent'] : '';
		$sids['aa'] = !empty($_COOKIE['dpsid-admin']) ? $_COOKIE['dpsid-admin'] : '';

		// i=u(user),a(agent),aa(admin)
		$interface = !empty($_GET['i']) ? $_GET['i'] : null;

		$pdo = $this->getPdo();
		$q = $pdo->prepare("UPDATE sessions SET date_last = ? WHERE id = ? AND auth = ?");
		$date = date('Y-m-d H:i:s');

		$sessions = array();
		foreach ($sids as $k => $sid) {
			if (!$sid || !strpos($sid, '-')) {
				continue;
			}

			list ($id, $auth) = explode('-', $sid, 2);
			$id = Util::baseDecode($id, Util::BASE36_ALPHABET);

			$sessions[$k] = array($id, $auth);

			$q->execute(array(
				$date,
				$id,
				$auth
			));
		}

		$token = null;
		if ($interface && isset($sessions[$interface])) {
			$secret = $this->getSetting('core.app_secret', 'APP_SECRET');
			$token = Util::generateStaticSecurityToken(md5(
				$sessions[$interface][0] . // id
				$sessions[$interface][1] . // auth
				$secret .
				'request_token'
			), 10800);
		}

		$content = json_encode(array(
			'okay' => true,
			'request_token' => $token
		));
		header("Content-Type: application/json; filename=session-ping.json");
		header('Content-Length: ' . strlen($content));
		header("Content-Disposition: inline; filename=session-ping.json");
		header('Last-Modified: ' . date('D, d M Y H:i:s', strtotime('-1 year')).' GMT');
		header('Expires: ' . date('D, d M Y H:i:s', strtotime('-1 year')).' GMT');
		header('Cache-Control: max-age=0,private');
		echo $content;
	}

	####################################################################################################################
	# requestSession
	####################################################################################################################

	protected function requestSessionAction()
	{
		$container = $this->bootFullSystem();

		$sessionObj = $container->get('session');
		$session_id = $sessionObj->getId();
		$session = $sessionObj->getEntity();

		$callback_name = false;
		if (isset($_GET['callback'])) {
			$callback_name = preg_replace('#[^a-zA-Z0-9_]#', '', $_GET['callback']);
		}

		$json = "{\"session_id\": \"$session_id\"}";

		if ($callback_name) {
			$content  = "$callback_name($json);";
			$filename = 'request-session.js';
			$filetype = 'text/javascript';
		} else {
			$content  = $json;
			$filename = 'request-session.json';
			$filetype = 'application/json';
		}

		header("Content-Type: $filetype; filename=$filename");
		header('Content-Length: ' . strlen($content));
		header("Content-Disposition: inline; filename=$filename");
		header('Last-Modified: ' . date('D, d M Y H:i:s', strtotime('-1 year')).' GMT');
		header('Expires: ' . date('D, d M Y H:i:s', strtotime('-1 year')).' GMT');
		header('Cache-Control: max-age=0,private');
		echo $content;
	}

	####################################################################################################################
	# isChatAvailableAction
	####################################################################################################################

	protected function isChatAvailableAction()
	{
		#------------------------------
		# Chat is available
		#------------------------------

		$online_time = 0;
		if (file_exists(dp_get_data_dir() . '/chat_is_available.trigger')) {
			$online_time = file_get_contents(dp_get_data_dir() . '/chat_is_available.trigger');
		}

		if ($online_time && $online_time > time() - 900) {

			$session_id = isset($_GET['__sid']) ? $_GET['__sid'] : null;
			if (!$session_id) {
				$session_id = isset($_COOKIE['dpsid']) ? $_COOKIE['dpsid'] : null;
			}

			$chat_id = isset($_COOKIE['dpchatid']) ? $_COOKIE['dpchatid'] : null;

			// they already have a chat active, load up system to get read to resume
			if ($session_id && $chat_id) {
				$to_login_page = false;

				$container = $this->bootFullSystem();

				$sessionObj = $container->get('session');
				$session_id = $sessionObj->getId();
				$session = $sessionObj->getEntity();
				$chat_manager = $container->getSystemObject('user_chat_manager', array('session' => $session));

				// True to allow fetching of chats w/ timeout
				$convo = $chat_manager->getChat(true);

				// If the user is on a new page, tell the agent
				if ($convo) {
					// If the status is ended then it's because of a timeout, but the user is back! so pop open the chat again
					if ($convo['status'] == 'ended') {
						$chat_manager->reopenTimoutChat($convo);
					}

					$current_page = !empty($_GET['current_page']) ? strval($_GET['current_page']) : false;
					if (!$current_page) {
						$current_page = $session->getVisitor()->getLastPage();
					} else if ($current_page != $session->getVisitor()->getLastPage()) {
						$vis = $session->getVisitor();
						$vis['last_page'] = $current_page;

						if ($session->getIsNew() || !$vis['session_landing_page']) {
							$vis['session_landing_page'] = $current_page;
						}

						if (!$vis['landing_page']) {
							$vis['landing_page'] = $current_page;
						}
						$container->getOrm()->persist($vis);
						$container->getOrm()->flush();
					}

					$chat_manager->addUserTrack($convo, $current_page);
					$container->getDb()->insert('chat_conversation_pings', array('chat_id' => $convo->getId(), 'ping_time' => time()));

					$cookie = new \Application\DeskPRO\HttpFoundation\Cookie('dpchatid', $convo->getId());
					$cookie->send();
				} else {
					$cookie = new \Application\DeskPRO\HttpFoundation\Cookie('dpchatid', 0, time() - 3600);
					$cookie->send();
				}
			} else {
				$to_login_page = false;
				$convo = false;
				$session_id = null;
			}

			$content = '';
			if ($convo) {
				$content .= "DpChatWidget.doResume = true;\n";
				if ($convo->is_window) {
					$content .= "DpChatWidget.isWindowChat = true;\n";
				}
			}
			if ($to_login_page) {
				$content .= "DpChatWidget.toLoginPage = true;\n";
			}

			if ($session_id) {
				$content .= "DpChatWidget.initWidget('$session_id');";
			} else {
				$content .= "DpChatWidget.initWidget(null);";
			}

		#------------------------------
		# Chat unavailable
		#------------------------------

		} else {
			$content = "DpChatWidget.setNotAvailable();\n";
		}

		header('Content-Type: text/javascript; filename=is-chat-available.js');
		header('Content-Length: ' . strlen($content));
		header('Content-Disposition: inline; filename=is-chat-available.js');
		header('Last-Modified: ' . date('D, d M Y H:i:s', strtotime('-1 year')).' GMT');
		header('Expires: ' . date('D, d M Y H:i:s', strtotime('-1 year')).' GMT');
		header('Cache-Control: max-age=0,private');
		echo $content;
	}

	public function agentLanguageAction($language_id)
	{
		$language_id = intval($language_id);
		$no_cache = !empty($_GET['nocache']);
		$js = false;
		$cache_file = dp_get_tmp_dir() . '/agent-lang-' . $language_id . '.cache';

		if (!$no_cache) {
			if (file_exists($cache_file)) {
				$data = @unserialize(@file_get_contents($cache_file));
				if (is_string($data)) {
					$js = $data;
				}
			}
		}

		if (!$js) {
			$container = $this->bootFullSystem();

			$tr = $container->getTranslator();
			$lang = $container->getEm()->getRepository('DeskPRO:Language')->find($language_id);
			if ($lang) {
				$tr->setLanguage($lang);
			} else {
				$language_id = 0;
				$cache_file = dp_get_tmp_dir() . '/agent-lang-' . $language_id . '.cache';
			}

			$js_phrases = array();
			$js_phrases['agent.general.add_a_label'] = $tr->getPhraseText('agent.general.add_a_label');
			$js_phrases['agent.general.check_on']    = $tr->getPhraseText('agent.general.check_on');
			$js_phrases['agent.general.check_off']   = $tr->getPhraseText('agent.general.check_off');

			$js_phrases["agent.general.reltime_less_second"]    = $tr->getPhraseText("agent.general.reltime_less_second");
			$js_phrases["agent.general.reltime_less_minute"]    = $tr->getPhraseText("agent.general.reltime_less_minute");
			$js_phrases["agent.general.reltimeago_less_second"] = $tr->getPhraseText("agent.general.reltimeago_less_second");
			$js_phrases["agent.general.reltimeago_less_minute"] = $tr->getPhraseText("agent.general.reltimeago_less_minute");

			foreach (array('reltime', 'reltimeago') as $pre) {
				foreach (array('second', 'minute', 'hour', 'day', 'week', 'month', 'year') as $name) {
					$js_phrases["agent.general.{$pre}_1_{$name}"] = $tr->getPhraseText("agent.general.{$pre}_1_{$name}");
					$js_phrases["agent.general.{$pre}_x_{$name}"] = $tr->getPhraseText("agent.general.{$pre}_x_{$name}");
				}
			}

			$add_phrases = array(
				'agent.userchat.message_started',
				'agent.userchat.transcript_sent',
				'agent.userchat.message_user_joined',
				'agent.userchat.message_user_left',
				'agent.userchat.message_set_department',
				'agent.userchat.assigned_to',
				'agent.userchat.msg_new_user_track',
				'agent.userchat.unassigned',
				'agent.userchat.msg_agent_timeout',
				'agent.userchat.msg_user_timeout',
				'agent.userchat.ended_by',
				'agent.userchat.message_ended-by',
				'agent.userchat.message_ended-by-user',
				'agent.userchat.ended',
				'agent.userchat.ended_user',
				'agent.userchat.message_started',
				'agent.userchat.message_user-joined',
				'agent.userchat.message_user-left',
				'agent.userchat.message_user-returned',
				'agent.userchat.message_set-department',
				'agent.userchat.message_assigned',
				'agent.userchat.message_unassigned',
				'agent.userchat.message_agent-timeout',
				'agent.userchat.message_wait-timeout',
				'agent.userchat.message_user-timeout',
				'agent.userchat.message_ended_by',
				'agent.userchat.message_ended',
				'agent.userchat.message_ended_user',
				'agent.userchat.window_open-new',
				'agent.userchat.window_start-button',
				'agent.userchat.resume-button',
			);

			foreach ($add_phrases as $k) {
				$js_phrases[$k] = $tr->getPhraseText($k);
			}

			$js = "window.DESKPRO_LANG = " . json_encode($js_phrases) . ";";

			if (!$no_cache) {
				$cache_slam_file = $cache_file . '.slam';
				if (!file_exists($cache_slam_file) || time() - filemtime($cache_slam_file) > 30) {
					$slam_fp = @fopen($cache_slam_file, 'w');
					if ($slam_fp && @flock($slam_fp, \LOCK_EX)) {
						@file_put_contents($cache_file, serialize($js), \LOCK_EX);
						@flock($slam_fp, \LOCK_UN);
						@fclose($slam_fp);
						@unlink($cache_slam_file);
					} else {
						@fclose($slam_fp);
					}
				}
			}
		}

		header('Content-Type: application/javascript; charset=utf-8');
		header('Content-Length: ' . strlen($js));
		header('Content-Disposition: inline; filename=agent-lang-' . $language_id . '.js');
		header('Last-Modified: ' . date('D, d M Y H:i:s', strtotime('-1 year')).' GMT');
		header('Expires: ' . date('D, d M Y H:i:s', strtotime('+1 year')).' GMT');
		echo $js;
	}

	public function userLanguageAction($language_id)
	{
		$language_id = intval($language_id);
		$no_cache = !empty($_GET['nocache']);
		$js = false;
		$cache_file = dp_get_tmp_dir() . '/user-lang-' . $language_id . '.cache';

		if (!$no_cache) {
			if (file_exists($cache_file)) {
				$data = @unserialize(@file_get_contents($cache_file));
				if (is_string($data)) {
					$js = $data;
				}
			}
		}

		if (!$js) {
			$container = $this->bootFullSystem();

			$tr = $container->getTranslator();
			$lang = $container->getEm()->getRepository('DeskPRO:Language')->find($language_id);
			if ($lang) {
				$tr->setLanguage($lang);
			} else {
				$language_id = 0;
				$cache_file = dp_get_tmp_dir() . '/user-lang-' . $language_id . '.cache';
			}

			$js_phrases = array();

			$js_phrases["user.time.time_less_second"] = $tr->phrase("user.time.time_less_second");
			$js_phrases["user.time.time-ago_less_second"] = $tr->phrase("user.time.time_less_second");

			foreach (array('time', 'time-ago') as $pre) {
				foreach (array('second', 'minute', 'hour', 'day', 'week', 'month', 'year') as $name) {
					$js_phrases["user.time.{$pre}_1_{$name}"] = $tr->phrase("user.time.{$pre}_1_{$name}");
					$js_phrases["user.time.{$pre}_x_{$name}"] = $tr->phrase("user.time.{$pre}_x_{$name}");
				}
			}

			$add_phrases = array(
				'user.chat.message_started',
				'user.chat.transcript_sent',
				'user.chat.message_user-joined',
				'user.chat.message_user-left',
				'user.chat.message_user-returned',
				'user.chat.message_set-department',
				'user.chat.message_assigned',
				'user.chat.message_unassigned',
				'user.chat.message_agent-timeout',
				'user.chat.message_user-timeout',
				'user.chat.message_ended_by',
				'user.chat.message_ended',
				'user.chat.message_ended_user',
				'user.chat.window_open-new',
				'user.chat.window_start-button',
				'user.chat.resume-button',
			);

			foreach ($add_phrases as $k) {
				$js_phrases[$k] = $tr->getPhraseText($k);
			}

			$js = "window.DESKPRO_LANG = " . json_encode($js_phrases) . ";";

			if (!$no_cache) {
				$cache_slam_file = $cache_file . '.slam';
				if (!file_exists($cache_slam_file) || time() - filemtime($cache_slam_file) > 30) {
					$slam_fp = @fopen($cache_slam_file, 'w');
					if ($slam_fp && @flock($slam_fp, \LOCK_EX)) {
						@file_put_contents($cache_file, serialize($js), \LOCK_EX);
						@flock($slam_fp, \LOCK_UN);
						@fclose($slam_fp);
						@unlink($cache_slam_file);
					} else {
						@fclose($slam_fp);
					}
				}
			}
		}

		header('Content-Type: application/javascript; charset=utf-8');
		header('Content-Length: ' . strlen($js));
		header('Content-Disposition: inline; filename=user-lang-' . $language_id . '.js');
		header('Last-Modified: ' . date('D, d M Y H:i:s', strtotime('-1 year')).' GMT');
		header('Expires: ' . date('D, d M Y H:i:s', strtotime('+1 year')).' GMT');
		echo $js;
	}
}

$dp_loader = new DpLoader();
$dp_loader->run();
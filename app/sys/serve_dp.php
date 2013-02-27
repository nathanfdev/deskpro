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

use Orb\Util\Util;

require_once DP_ROOT.'/sys/serve_abstract.php';

/**
 * A light-weight loader for website widgetss
 */
class DpLoader extends LoaderAbstract
{
	public function runAction()
	{
		try {
			$pathinfo = $this->getPathInfo();

			if (preg_match('#^/vis\.js#', $pathinfo)) {
				$this->visitorPingAction();

			} elseif (preg_match('#^/chat/is-available\.js#', $pathinfo)) {
				// Legacy
				$_GET['chat'] = true;
				$this->visitorPingAction();

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
	# visitorPing
	####################################################################################################################

	protected function visitorPingAction()
	{
		$visitor_id   = null;
		$visitor_code = null;
		$visitor      = null;

		if (isset($_REQUEST['vc'])) {
			$visitor_code = (string)$_REQUEST['vc'];
		} elseif (isset($_COOKIE['dpvc'])) {
			$visitor_code = (string)$_COOKIE['dpvc'];
		}

		if ($visitor_code && !strpos($visitor_code, '-')) {
			$visitor_code = null;
		}

		$js_out = array();

		#-----------------------------------
		# Authorize a visitor id
		#-----------------------------------

		if ($visitor_code) {
			list ($visitor_id, $visitor_auth) = explode('-', $visitor_code, 2);
			$visitor_id = (int)$visitor_id;

			$q = $this->getPdo()->prepare("
				SELECT
					visitors.id, visitors.person_id, visitors.initial_track_id, visitors.visit_track_id, visitors.auth, visitors.chat_invite, visitors.page_count, visitors.date_last,
					visitor_tracks.date_created AS date_last_track
				FROM visitors
				LEFT JOIN visitor_tracks ON (visitor_tracks.id = visitors.last_track_id)
				WHERE visitors.id = ?
			");
			$q->execute(array($visitor_id));
			$visitor = $q->fetch(\PDO::FETCH_ASSOC);

			if (!$visitor || $visitor['auth'] != $visitor_auth) {
				$visitor_id = null;
				$visitor_code = null;
				$visitor = null;
			}
		}

		#-----------------------------------
		# If we have no visitor, create one
		#-----------------------------------

		$is_new_visit_session = false;
		$is_new_visitor = false;

		if (!$visitor) {
			$is_new_visitor = true;
			$is_new_visit_session = true;

			$visitor = array(
				'auth'             => '',
				'person_id'        => null,
				'page_count'       => 1,
				'date_created'     => date('Y-m-d H:i:s'),
				'date_last'        => date('Y-m-d H:i:s'),
				'initial_track_id' => null,
				'visit_track_id'   => null,
				'last_track_id'    => null,
			);

			$tmp = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
			for ($i = 0; $i < 15; $i++) {
				$t = mt_rand(0, 35);
				$visitor['auth'] .= $tmp[$t];
			}

			$this->getPdo()->prepare("
				INSERT INTO visitors
				SET auth = ?, page_count = 1, date_created = ?, date_last = ?
			")->execute(array(
				$visitor['auth'],
				$visitor['date_created'],
				$visitor['date_last'],
			));

			$visitor_id = $visitor['id'] = $this->getPdo()->lastInsertId();
		} else {
			$is_new_visitor = false;

			if ($visitor['date_last_track']) {
				$last_time = strtotime($visitor['date_last']);

				if ($last_time < (time() - 2400)) {
					$is_new_visit_session = true;
				}
			} else {
				$is_new_visit_session = true;
			}
		}

		$visitor_code = "{$visitor['id']}-{$visitor['auth']}";

		#-----------------------------------
		# Create the track
		#-----------------------------------

		$visitor_track = array();
		$visitor_track['visitor_id']   = $visitor_id;
		$visitor_track['is_new_visit'] = $is_new_visit_session;
		if (!empty($_REQUEST['url'])) {
			$visitor_track['page_url'] = (string)$_REQUEST['url'];
		} elseif (!empty($_SERVER['HTTP_REFERER'])) {
			$visitor_track['page_url'] = (string)$_SERVER['HTTP_REFERER'];
		} else {
			$visitor_track['page_url'] = '<unknown>';
		}

		if (!empty($_REQUEST['title'])) {
			$visitor_track['page_title'] = (string)$_REQUEST['title'];
		}
		if (!empty($_REQUEST['rurl'])) {
			$visitor_track['ref_page_url'] = (string)$_REQUEST['rurl'];
		}

		$visitor_track['user_agent']   = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Unknown';
		$visitor_track['user_browser'] = '';
		$visitor_track['user_os']      = '';
		$visitor_track['ip_address']   = $_SERVER['REMOTE_ADDR'];
		$visitor_track['date_created'] = date('Y-m-d H:i:s');

		if (dp_get_config('trust_proxy_data') && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$visitor_track['ip_address'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}

		if ($is_new_visit_session && function_exists('geoip_record_by_name')) {
			if ($geo = @geoip_record_by_name($visitor_track['ip_address'])) {
				if (!empty($geo['continent_code'])) $visitor_track['geo_continent'] = $geo['continent_code'];
				if (!empty($geo['country_code']))   $visitor_track['geo_country']   = $geo['country_code'];
				if (!empty($geo['region']))         $visitor_track['geo_region']    = $geo['region'];
				if (!empty($geo['city']))           $visitor_track['geo_city']      = $geo['city'];
				if (!empty($geo['longitude']))      $visitor_track['geo_long']      = $geo['longitude'];
				if (!empty($geo['latitude']))       $visitor_track['geo_lat']       = $geo['latitude'];
			} elseif ($geo_country = @geoip_country_code_by_name($visitor_track['ip_address'])) {
				$visitor_track['geo_country'] = $geo_country;
				if ($geo_continent = @geoip_continent_code_by_name($visitor_track['ip_address'])) {
					$visitor_track['geo_continent'] = $geo_continent;
				}
			}
		}

		$set_q = array();
		foreach ($visitor_track as $k => $v) {
			$set_q[] = "$k = ?";
		}
		$set_q = implode(', ', $set_q);

		$this->getPdo()->prepare("
			INSERT INTO visitor_tracks
			SET $set_q
		")->execute(array_values($visitor_track));

		$visitor_track['id'] = $this->getPdo()->lastInsertId();

		#-----------------------------------
		# Update the last times for the visitor
		#-----------------------------------

		$visitor_update = array();
		if (!$visitor['initial_track_id']) {
			$visitor_update['initial_track_id'] = $visitor_track['id'];
		}
		if (!$visitor['visit_track_id'] || $is_new_visit_session) {
			$visitor_update['visit_track_id'] = $visitor_track['id'];
		}
		$visitor_update['last_track_id'] = $visitor_track['id'];
		$visitor_update['date_last']     = date('Y-m-d H:i:s');

		if (!$is_new_visitor) {
			$visitor_update['page_count'] = $visitor['page_count'] + 1;
		}

		$set_q = array();
		foreach ($visitor_update as $k => $v) {
			$set_q[] = "$k = ?";
		}
		$set_q = implode(', ', $set_q);

		$q = $this->getPdo()->prepare("
			UPDATE visitors
			SET $set_q
			WHERE id = {$visitor_id}
		")->execute(array_values($visitor_update));

		$js_out[] = "window.DESKPRO_VISITOR_ID = '$visitor_code';";
		$js_out[] = "if (window.DpVisLoaded) window.DpVisLoaded();";

		#------------------------------
		# Chat is available
		#------------------------------

		$session_id = null;

		if (isset($_GET['chat'])) {
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
						if ($current_page) {
							$chat_manager->addUserTrack($convo, $current_page);
						}
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

				if ($convo) {
					$js_out[] = "DpChatWidget.doResume = true;\n";
					if ($convo->is_window) {
						$js_out[] = "DpChatWidget.isWindowChat = true;\n";
					}
				}
				if ($to_login_page) {
					$js_out[] = "DpChatWidget.toLoginPage = true;\n";
				}

				if ($session_id) {
					$js_out[] = "DpChatWidget.initWidget('$session_id');";

					// Connect the visitor to the session
					$this->getPdo()->prepare("UPDATE sessions SET visitor_id = ? WHERE id = ?")->execute(array(
						$visitor['id'],
						$session_id
					));

					// Connect the chat as well
					if ($convo) {
						$this->getPdo()->prepare("
							UPDATE chat_conversations
							SET visitor_id = ? WHERE id = ?
						")->execute(array(
							$visitor['id'],
							$convo->getId()
						));
					}
				} else {
					$js_out[] = "DpChatWidget.initWidget(null);";
				}

			// Chat unavailable
			} else {
				$js_out[] = "DpChatWidget.setNotAvailable();\n";
			}
		}

		#------------------------------
		# Try to connect an authenticated user
		# with a visitor if they have a session
		# cookie as well
		#------------------------------

		$visitor_person_id = null;

		// We loaded chat, in which case
		// we have the full session already
		if (isset($sessionObj)) {
			$visitor_person_id = $sessionObj->getPerson()->getId();

		// Otherwise we need to query the sessions table
		} else {
			$session_code = isset($_GET['dpsid']) ? $_GET['dpsid'] : null;
			if (!$session_code) {
				$session_code = isset($_COOKIE['dpsid']) ? $_COOKIE['dpsid'] : null;
			}
			if (!$session_code) {
				$session_code = isset($_COOKIE['dpsid-agent']) ? $_COOKIE['dpsid-agent'] : null;
			}
			if (!$session_code) {
				$session_code = isset($_COOKIE['dpsid-admin']) ? $_COOKIE['dpsid-admin'] : null;
			}

			if ($session_code && strpos($session_code, '-')) {
				list ($session_id, $session_auth) = explode('-', $session_code, 2);

				$session_id = Util::baseDecode($session_id, Util::BASE36_ALPHABET);

				$q = $this->getPdo()->prepare("
					SELECT person_id
					FROM sessions
					WHERE id = ? AND auth = ?
				");
				$q->execute(array($session_id, $session_auth));

				$visitor_person_id = $q->fetchColumn();
			}
		}

		if ($visitor_person_id && $visitor_person_id != $visitor['person_id']) {
			$this->getPdo()->prepare("
				UPDATE visitors
				SET person_id = ?
				WHERE id = ?
			")->execute(array($visitor_person_id, $visitor['id']));
		}

		#-----------------------------------
		# Output
		#-----------------------------------

		$js_out = implode("\n", $js_out);

		setcookie('dpvc', $visitor_code, time() + 15552000, '/', null);
		header('Content-Type: text/javascript; filename=vis.js');
		header('Content-Length: ' . strlen($js_out));
		header('Content-Disposition: inline; filename=vis.js');
		header('Last-Modified: ' . date('D, d M Y H:i:s', strtotime('-1 year')).' GMT');
		header('Expires: ' . date('D, d M Y H:i:s', strtotime('-1 year')).' GMT');
		header('Cache-Control: max-age=0,private');
		echo $js_out;
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
	# agentLanguage
	####################################################################################################################

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
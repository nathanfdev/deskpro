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

					$chat_manager->addUserTrack($convo, $session->getVisitor()->getLastPage());
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
}

$dp_loader = new DpLoader();
$dp_loader->run();
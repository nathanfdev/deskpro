<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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
 * @category Api
 */

namespace Application\ApiBundle\Request;

use Application\ApiBundle\ApiUser;
use Application\DeskPRO\Entity\ApiKey;
use Application\DeskPRO\Entity\ApiKeyLog;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

/**
 * Reads the request to determine which API context to run in.
 */
class RequestAuth
{
	/**
	 * @var \Application\ApiBundle\ApiUser
	 */
	private $api_user;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var \Symfony\Component\HttpFoundation\Request
	 */
	private $request;

    /**
     * @var \Application\DeskPRO\Entity\ApiKeyLog
     */
    protected $log_entry;


	/**
	 * @param EntityManager $em
	 * @param Request $request
	 */
	public function __construct(EntityManager $em, Request $request)
	{
		$this->em = $em;
		$this->request = $request;
	}


	/**
	 * @return \Application\ApiBundle\ApiUser
	 */
	public function getApiUser()
	{
		if ($this->api_user == null) {
			$this->api_user = new ApiUser();
			$this->api_user->request_token = $this->getRequestToken();

			#------------------------------
			# Get API key or token
			#------------------------------

			$this->api_user->api_key = $this->getApiKeyFromRequest();
			if (!$this->api_user->api_key) {
				$this->api_user->api_token = $this->getApiTokenFromRequest();

				if ($this->api_user->api_token) {
					// If we have a token, we might have a session as well
					// (depending on permission strategy, some api calls may require an actual logged-in user)
					$this->api_user->session = $this->getSessionFromRequest();
				}
			}

			#------------------------------
			# Get API key or token
			#------------------------------

			if ($this->api_user->api_key) {
				$this->api_user->person = $this->api_user->api_key->person;

				// API Keys can specify an agent ID context which overrides their own
				$agent_id = $this->getRequestValue('X-DeskPRO-Agent-ID', 'AGENT-ID', false);
				if (!$agent_id) {
					$agent_id = $this->getRequestValue('X-DeskPRO-Agent-ID', 'DP-AGENT-ID', false);
				}
				if ($agent_id && $this->api_user->api_key->isFlagSet(ApiKey::FLAG_SUPER_KEY)) {
					$agent = $this->em->getRepository('DeskPRO:Person')->find($agent_id);
					if ($agent && $agent->is_agent && !$agent->is_deleted) {
						$this->api_user->person = $agent;
					}
				}

                $this->createApiLogEntry();

			} else if ($this->api_user->api_token) {
				$this->api_user->person = $this->api_user->api_token->person;
			}
		}

		return $this->api_user;
	}


	/**
	 * @return \Application\DeskPRO\Entity\ApiKey|null
	 */
	private function getApiKeyFromRequest()
	{
		$key_str = $this->getRequestValue('X-DeskPRO-API-Key', 'API-KEY', true);
		if (!$key_str) {
			return null;
		}

		return $this->em->getRepository('DeskPRO:ApiKey')->findByKeyString($key_str);;
	}


	/**
	 * @return \Application\DeskPRO\Entity\ApiToken|null
	 */
	private function getApiTokenFromRequest()
	{
		$token_str = $this->getRequestValue('X-DeskPRO-API-Token', 'API-TOKEN', true);
		if (!$token_str) {
			return null;
		}

		return $this->em->getRepository('DeskPRO:ApiToken')->findByTokenString($token_str);;
	}


	/**
	 * @return \Application\DeskPRO\Entity\Session|null
	 */
	private function getSessionFromRequest()
	{
		$session_id = $this->getRequestValue('X-DeskPRO-Session-ID', 'SESSION-ID', true);
		if (!$session_id) {
			return null;
		}

		return $this->em->getRepository('DeskPRO:Session')->getSessionFromCode($session_id);
	}


	/**
	 * @return null|string
	 */
	private function getRequestToken()
	{
		$tok = $this->getRequestValue('X-DeskPRO-Request-Token', 'REQUEST-TOKEN', true);
		if (!$tok) {
			return null;
		}

		return $tok;
	}


	/**
	 * @param string $header_name  The name to look for in headers
	 * @param string $request_name The name to look for in request params
	 * @param bool $use_http_auth  To concat the user/pw is http auth
	 * @return string|null
	 */
	private function getRequestValue($header_name, $request_name, $use_http_auth = false)
	{
		$str = $this->request->headers->get($header_name, null, true);
		if ($str) {
			return $str;
		}

		if ($request_name && !empty($_REQUEST[$request_name]) && is_scalar($_REQUEST[$request_name])) {
			return trim($_REQUEST[$request_name]);
		}

		$headers = $this->request->server->getHeaders();
		if ($use_http_auth && !empty($headers['PHP_AUTH_USER']) && !empty($headers['PHP_AUTH_PW'])) {
			return $headers['PHP_AUTH_USER'].':'.$headers['PHP_AUTH_PW'];
		}

		return null;
	}

    /**
     *
     */
    protected function createApiLogEntry()
    {
        if (!$key = $this->api_user->api_key) {
            return;
        }

        $log = new ApiKeyLog();
        $log->key = $key;
        $log->request = array(
            'path' => $this->request->getPathInfo(),
            'method' => $this->request->getMethod(),
            'payload' => $this->request->request->all(),
        );
        $log->response = array(
            'status' => null,
            'content' => null, // parse json to array?
        );

        $this->em->persist($log);
        $this->em->flush($log);
        $this->log_entry = $log;
    }

    /**
     * @return \Application\DeskPRO\Entity\ApiKeyLog|null
     */
    public function getApiLogEntry()
    {
        return $this->log_entry;
    }
}
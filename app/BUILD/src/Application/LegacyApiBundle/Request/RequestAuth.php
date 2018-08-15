<?php

/**
 * DeskPRO.
 *
 * @category Api
 */

namespace Application\LegacyApiBundle\Request;

use Application\DeskPRO\Entity\ApiKey;
use Application\DeskPRO\Entity\ApiKeyLog;
use Application\DeskPRO\NewSettings\SettingsResolver;
use Application\LegacyApiBundle\ApiUser;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

/**
 * Reads the request to determine which API context to run in.
 */
class RequestAuth
{
    /**
     * @var \Application\LegacyApiBundle\ApiUser
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
     * @var SettingsResolver
     */
    private $settingsResolver;

    /**
     * @var \Application\DeskPRO\Entity\ApiKeyLog
     */
    protected $log_entry;

    /**
     * @param EntityManager    $em
     * @param Request          $request
     * @param SettingsResolver $settingsResolver
     */
    public function __construct(EntityManager $em, Request $request, SettingsResolver $settingsResolver)
    {
        $this->em               = $em;
        $this->request          = $request;
        $this->settingsResolver = $settingsResolver;
    }

    /**
     * @return \Application\LegacyApiBundle\ApiUser
     */
    public function getApiUser()
    {
        if ($this->api_user == null) {
            $this->api_user                = new ApiUser();
            $this->api_user->request_token = $this->getRequestToken();

            //------------------------------
            // Get API key or token
            //------------------------------

            $this->api_user->api_key = $this->getApiKeyFromRequest();
            if (!$this->api_user->api_key) {
                $this->api_user->api_token = $this->getApiTokenFromRequest();

                if ($this->api_user->api_token) {
                    // If we have a token, we might have a session as well
                    // (depending on permission strategy, some api calls may require an actual logged-in user)
                    $this->api_user->session = $this->getSessionFromRequest();
                }
            }

            //------------------------------
            // Get API key or token
            //------------------------------

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
            } elseif ($this->api_user->api_token) {
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
            return;
        }

        return $this->em->getRepository('DeskPRO:ApiKey')->findByKeyString($key_str);
    }

    /**
     * @return \Application\DeskPRO\Entity\ApiToken|null
     */
    private function getApiTokenFromRequest()
    {
        $token_str = $this->getRequestValue('X-DeskPRO-API-Token', 'API-TOKEN', true);
        if (!$token_str) {
            return;
        }

        return $this->em->getRepository('DeskPRO:ApiToken')->findByTokenString($token_str);
    }

    /**
     * @return \Application\DeskPRO\Entity\Session|null
     */
    private function getSessionFromRequest()
    {
        $session_id = $this->getRequestValue('X-DeskPRO-Session-ID', 'SESSION-ID', false);
        if (!$session_id) {
            if (isset($_COOKIE['dpsid-admin'])) {
                $session_id = $_COOKIE['dpsid-admin'];
            }
        }
        if (!$session_id) {
            return;
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
            return;
        }

        return $tok;
    }

    /**
     * @param string $header_name   The name to look for in headers
     * @param string $request_name  The name to look for in request params
     * @param bool   $use_http_auth To concat the user/pw is http auth
     *
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

        return;
    }

    protected function createApiLogEntry()
    {
        if (!$key = $this->api_user->api_key) {
            return;
        }

        $settings = $this->settingsResolver->getGlobalSettings();

        // api log is disabled
        if (!$settings->get('api_log.enabled')) {
            return;
        }

        // api log is disabled for api keys
        $modes = $settings->getSerializedArray('api_log.modes', []);
        if (!in_array('key', $modes)) {
            return;
        }

        $log = new ApiKeyLog();
        $log->setKey($key);
        $log->setRequest([
            'path'    => $this->request->getPathInfo(),
            'method'  => $this->request->getMethod(),
            'payload' => $this->request->request->all(),
        ]);
        $log->setResponse([
            'status'  => null,
            'content' => null, // parse json to array?
        ]);

        $this->em->persist($log);
        $this->em->flush(); // flushing only one entity leads to losing changesets and sync status in UoW
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

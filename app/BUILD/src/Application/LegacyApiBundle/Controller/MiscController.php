<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Auth\LoginProcessor;
use Application\DeskPRO\Entity\ApiKey;
use Application\DeskPRO\Entity\ApiToken;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Session;
use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\LoginLogs\LoginLogs;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\TokenExchangeAbuseCheck;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Exception\AntiAbuseException;
use Orb\Util\Strings;
use Orb\Util\Util;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

/**
 * @ApiModes("all")
 */
class MiscController extends AbstractController
{
    /**
     * {@inheritdoc}
     */
    public function preActionHandler(Request $request, $action, $arguments = null)
    {
        if ($action == 'tokenExchangeAction' || $action == 'helpdeskInfoAction' || $action == 'dpSpecialAction') {
            return;
        }

        return parent::preActionHandler($request, $action, $arguments);
    }

    protected function _checkRateLimit($action, $arguments = null)
    {
        if ($action == 'getRateLimitAction') {
            return;
        }

        return parent::_checkRateLimit($action, $arguments);
    }

    protected function _updateRateLimit($action, $arguments = null)
    {
        if ($action == 'getRateLimitAction') {
            return;
        }

        parent::_updateRateLimit($action, $arguments);
    }

    public function helpdeskInfoAction()
    {
        $data = [];

        // The home page for the helpdesk (used in links and such)
        $data['helpdesk_url'] = trim(str_replace(
                '/index.php',
                '',
                $this->container->getBrandSetting('core.deskpro_url')),
                '/'
            ).'/';

        // The base URL for deskpro URLs (will include /index.php/ if required)
        $data['deskpro_url'] = $data['helpdesk_url'];

        if (defined('DPC_SITE_DOMAIN')) {
            $data['api_url']    = 'https://'.DPC_SITE_DOMAIN.'/index.php/api/';
            $data['asset_url']  = '//'.DPC_SITE_DOMAIN.'/web/';
            $data['widget_url'] = '//'.DPC_SITE_DOMAIN.'/';
        } else {
            $data['api_url'] = $data['helpdesk_url'].'index.php/api/';

            $data['asset_url'] = $this->container->getBrandSetting('core.deskpro_url');
            $data['asset_url'] = trim(str_replace('/index.php', '', $data['asset_url']), '/');
            $data['asset_url'] .= '/web/';
            $data['asset_url'] = preg_replace('#^https?://#', '//', $data['asset_url']);

            $data['widget_url'] = $data['deskpro_url'];
            $data['widget_url'] = preg_replace('#^https?://#', '//', $data['widget_url']);
        }

        return $this->createApiResponse($data);
    }

    private function _authLocalInput($email, $password)
    {
        //------------------------------
        // Auth local
        //------------------------------

        $adapter = new \Application\DeskPRO\Auth\Adapter\Local($this->container->getEm());
        $adapter->setCredentials($email, $password);
        $result = $adapter->authenticate();

        if ($result->isValid()) {
            $person = $this->em->getRepository(Person::class)->find($result->getIdentity()->getIdentity());

            if ($person) {
                $identity = new \Orb\Auth\Identity($person->id, ['person' => $person]);
                $result   = new \Orb\Auth\Result(\Orb\Auth\Result::SUCCESS, $identity);

                return $result;
            }
        }

        //------------------------------
        // Auth usersources that accept local input
        //------------------------------

        $usersources = $this->em->getRepository(Usersource::class)->getLocalInputUsersources();
        foreach ($usersources as $us) {

            /* @var $us \Application\DeskPRO\Entity\Usersource */
            $adapter = $us->getAdapter()->getAuthAdapter();
            $adapter->setFormData([
                'username' => $email,
                'password' => $password,
            ]);

            try {
                $result = $adapter->authenticate();
            } catch (\Exception $e) {
                continue;
            }

            if ($result->isValid()) {
                $login_processor = new LoginProcessor($us, $result->getIdentity());
                $person          = $login_processor->getPerson();

                $identity = new \Orb\Auth\Identity($person->id, ['person' => $person]);
                $result   = new \Orb\Auth\Result(\Orb\Auth\Result::SUCCESS, $identity);

                return $result;
            }
        }

        return new \Orb\Auth\Result(\Orb\Auth\Result::FAILURE_INVALID_CREDS);
    }

    public function tokenExchangeAction(Request $request)
    {
        $email = $this->in->getString('email');
        $check = new TokenExchangeAbuseCheck($email, $request->getClientIp());

        try {
            $this->getContainer()->get('anti_abuse')->check($check);
        } catch (AntiAbuseException $e) {
            return $this->createApiErrorResponse(
                'account_locked',
                sprintf('Account locked for %d seconds', $check->getLockoutTime()),
                403
            );
        }

        if ($check->isLimited()) {
            return $this->createApiErrorResponse('rate_limit_exceeded', 'Rate Limit Exceeded', 403);
        }

        $result = $this->_authLocalInput($this->in->getString('email'), $this->in->getString('password'));

        if (!$result->isValid()) {

            // Send alert
            $attemptPerson = $this->em->getRepository(Person::class)->findOneByEmail($this->in->getString('email'));
            if ($attemptPerson && $attemptPerson->getPref('agent_notif.login_attempt_fail.email')) {
                if ($this->get('deskpro.feature_flags')->hasBeta('email_templates')) {
                    $viewModel = $this->get('email.agent_viewmodel_factory')
                        ->createLoginAlertModel(
                            $request,
                            $this->session->getEntity()->getDateCreated(),
                            false
                        );
                    $this->get('email.email_sender')
                        ->send($viewModel, ['to' => $attemptPerson]);
                } else {
                    $message = $this->container->getMailer()->createMessage();

                    $message->setTemplate(
                        'DeskPRO:emails_agent:login-alert.html.twig',
                        [
                            'success'   => false,
                            'firstSeen' => $this->session->getEntity()->getDateCreated(),
                            'request'   => $request,
                        ]
                    );
                    $message->setTo($attemptPerson->getPrimaryEmailAddress(), $attemptPerson->getDisplayName());
                    $this->container->getMailer()->send($message);
                }
            }

            // Save login log
            if ($attemptPerson) {
                $this->db->insert('login_log', [
                    'person_id'    => $attemptPerson->getId(),
                    'area'         => 'api',
                    'is_success'   => 0,
                    'ip_address'   => $request->getClientIp(),
                    'hostname'     => @gethostbyaddr($request->getClientIp()) ?: '',
                    'user_agent'   => empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'],
                    'date_created' => date('Y-m-d H:i:s'),
                ]);
            }

            return $this->createApiErrorResponse('invalid_login', 'Invalid login details', 403);
        }

        $identity = $result->getIdentity();

        $person = isset($identity['person']) ? $identity['person'] : null;

        if (!$person || $person->is_disabled || !$person->is_agent) {
            return $this->createApiErrorResponse('invalid_login', 'Cannot use the API with that person', 403);
        }

        App::setCurrentPerson($person);

        if ($person->getPref('agent_notif.login_attempt.email')) {
            if ($this->get('deskpro.feature_flags')->hasBeta('email_templates')) {
                $viewModel = $this->get('email.agent_viewmodel_factory')
                    ->createLoginAlertModel(
                        $request,
                        $this->session->getEntity()->getDateCreated(),
                        true
                    );
                $this->get('email.email_sender')
                    ->send($viewModel, ['to' => $person]);
            } else {
                $message = $this->container->getMailer()->createMessage();
                $message->setTemplate(
                    'DeskPRO:emails_agent:login-alert.html.twig',
                    [
                        'success'   => true,
                        'firstSeen' => $this->session->getEntity()->getDateCreated(),
                        'request'   => $request,
                    ]
                );
                $message->setTo($person->getPrimaryEmailAddress(), $person->getDisplayName());
                $this->container->getMailer()->send($message);
            }
        }

        // Login log
        $this->db->insert('login_log', [
            'person_id'    => $person->getId(),
            'area'         => 'api',
            'is_success'   => 1,
            'ip_address'   => $request->getClientIp(),
            'hostname'     => @gethostbyaddr($request->getClientIp()) ?: '',
            'user_agent'   => empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'],
            'date_created' => date('Y-m-d H:i:s'),
        ]);

        /** @var ApiToken $token */
        $token = $this->em->getRepository(ApiToken::class)->getTokenForPerson($person);
        if (!$token) {
            $token         = new \Application\DeskPRO\Entity\ApiToken();
            $token->scope  = 'client';
            $token->person = $person;
        } elseif ($token->date_expires && $token->date_expires->getTimestamp() < time()) {
            $token->regenerateToken();
        }
        $token->date_expires = null;

        $this->em->persist($token);
        $this->em->flush();

        $data = [
            'success'   => true,
            'api_token' => $token->getKeyString(),
        ];

        if ($this->in->getBool('return_info')) {
            $api_url = $this->container->getBrandSetting('core.deskpro_url');
            $api_url .= 'index.php/';

            if ($request->isSecure() && strpos($api_url, 'https://') !== 0 && !defined('DPC_IS_CLOUD')) {
                $api_url = preg_replace('#^http://#', 'https://', $api_url);
            }

            $data['api_url']       = $api_url;
            $data['helpdesk_info'] = [
                'url'  => $this->container->getBrandSetting('core.deskpro_url'),
                'name' => $this->container->getBrandSetting('core.helpdesk_name'),
            ];
            $data['person_id']   = $person->getId();
            $data['person_info'] = $person->toApiData(true);
        }

        return $this->createApiResponse($data);
    }

    public function renewTokenAction()
    {
        $person = $this->person;

        $token = $this->em->getRepository(ApiToken::class)->getTokenForPerson($person);
        if (!$token) {
            $token         = new \Application\DeskPRO\Entity\ApiToken();
            $token->person = $person;
        } elseif ($token->date_expires && $token->date_expires->getTimestamp() < time()) {
            $token->regenerateToken();
        }
        $token->date_expires = null;

        $this->em->persist($token);
        $this->em->flush();

        $data = [
            'success'   => true,
            'api_token' => $token->getKeyString(),
        ];

        if ($this->in->getBool('return_info')) {
            $api_url = $this->container->getBrandSetting('core.deskpro_url');

            $data['api_url']       = $api_url;
            $data['helpdesk_info'] = [
                'url'  => $this->container->getBrandSetting('core.deskpro_url'),
                'name' => $this->container->getBrandSetting('core.helpdesk_name'),
            ];
            $data['person_id']   = $person->getId();
            $data['person_info'] = $person->toApiData(true);
        }

        return $this->createApiResponse($data);
    }

    public function uploadAction()
    {
        $accept = $this->container->getAttachmentAccepter();
        $error  = null;

        $path = $this->in->getString('path');
        if ($path && strpos($path, 'dp_file:icons:') === 0) {
            $path = preg_replace('#^dp_file:icons:(\.\./){4}#', DP_WEB_ROOT.'/web/', $path);
            $path = preg_replace('#^dp_file:icons:/?ASSET_DIR#', DP_WEB_ROOT.'/web/', $path);
            $path = str_replace('\\', '/', $path);
            $path = realpath($path);
            if (!$path || !is_file($path) || strpos($path, DP_WEB_ROOT) !== 0 || Strings::getExtension($path) != 'png') {
                throw $this->createNotFoundException();
            }

            $blob = $this->container->getBlobStorage()->createBlobRecordFromFile($path, pathinfo($path, PATHINFO_BASENAME), 'image/png');
        } else {
            $file  = $this->request->files->get('file');
            $error = $accept->getError($file, 'agent');

            if (!$error && $this->in->getBool('is_image')) {
                $set = new \Application\DeskPRO\Attachments\RestrictionSet();
                $set->setAllowedExts(['gif', 'png', 'jpg', 'jpeg']);
                $accept->addRestrictionSet('only_images', $set);
                $error = $accept->getError($file, 'only_images', true);
            }
            if ($error) {
                $message = $this->container->getTranslator()->phrase('agent.general.attach_error_'.$error['error_code'], $error);

                return $this->createApiErrorResponse($error['error_code'], $message);
            }

            $blob = $accept->accept($file);
        }

        return $this->createApiResponse(['blob' => $blob->toApiData()]);
    }

    public function getSessionPersonAction($session_code)
    {
        $session = $this->em->getRepository(Session::class)->getSessionFromCode($session_code);
        if (!$session) {
            return $this->createApiErrorResponse('no_session', 'session could not be found or could not be validated');
        }

        if ($session->person && $session->person->id) {
            return $this->createApiResponse(['person' => $session->person->toApiData()]);
        } else {
            return $this->createApiResponse(['person' => false]);
        }
    }

    public function getRateLimitAction()
    {
        if (!$this->container->getSetting('core.api_rate_limit')) {
            return $this->createApiResponse([
                'limit' => 0,
            ]);
        }

        if ($this->apikey) {
            $this->rate_info = $this->em->getRepository(ApiKey::class)->getRateLimitInfo($this->apikey);
        } else {
            $this->rate_info = $this->em->getRepository(ApiToken::class)->getRateLimitInfo($this->api_token);
        }

        return $this->createApiResponse([
            'limit'       => $this->container->getSetting('core.api_rate_limit'),
            'remaining'   => max(0, $this->container->getSetting('core.api_rate_limit') - $this->rate_info['hits']),
            'reset_stamp' => $this->rate_info['reset_stamp'],
            'reset_date'  => gmdate('r', $this->rate_info['reset_stamp']),
        ]);
    }

    public function getLastLoginAction()
    {
        if (!$this->person || !$this->person->id) {
            throw $this->createNotFoundException();
        }

        $login_logs = new LoginLogs($this->em, $this->container->getAgentData());
        $login_logs->setPage(1);
        $login_logs->setPerPage(2);
        $login_logs->setFilter($this->person);
        $records = $login_logs->getAll();

        array_shift($records); // will be the current login
        $log = array_shift($records); // will be the last login

        return $this->createJsonResponse(['last_login' => $log]);
    }

    /**
     * Special action codes (internal system use).
     *
     * @param string $action
     *
     * @return \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function dpSpecialAction($action)
    {
        if (!defined('DP_API_SPECIAL_CODE')) {
            throw $this->createAccessDeniedException('DP_API_SPECIAL_CODE is not defined');
        }

        if ($this->in->getString('SC') != DP_API_SPECIAL_CODE) {
            throw $this->createAccessDeniedException('DP_API_SPECIAL_CODE invalid');
        }

        switch ($action) {
            case 'agent_login_token':

                if (!($agent_id = $this->in->getUInt('agent_id'))) {
                    foreach ($this->container->getAgentData()->getAgents() as $agent) {
                        if ($agent->can_admin) {
                            $agent_id = $agent->id;
                            break;
                        }
                    }
                }

                $agent = $this->container->getAgentData()->get($agent_id);
                if (!$agent) {
                    throw $this->createNotFoundException();
                }

                $secret = sha1($agent->secret_string.$agent->salt);
                $token  = Util::generateStaticSecurityToken($secret, 300);

                $data = [
                    'agent_id'    => $agent->id,
                    'agent_name'  => $agent->getDisplayName(),
                    'agent_email' => $agent->getPrimaryEmailAddress(),
                    'valid_until' => date('Y-m-d H:i:s', time() + 300),
                    'login_token' => $token,
                    'login_url'   => $this->get('router')->generate('user', [], RouterInterface::ABSOLUTE_URL).'agent/login?tok='.$agent->getId().'-'.$token,
                ];

                return $this->createApiResponse($data);

            case 'list_agents':

                $data = ['agents' => []];

                foreach ($this->container->getAgentData()->getAgents() as $agent) {
                    $data['agents'][$agent->id] = [
                        'agent_id'    => $agent->id,
                        'agent_name'  => $agent->getDisplayName(),
                        'agent_email' => $agent->getPrimaryEmailAddress(),
                    ];
                }

                return $this->createApiResponse($data);
        }

        throw $this->createNotFoundException();
    }

    public function checkUrlAction(Request $request)
    {
        $scheme = $request->get('scheme');
        $host   = $request->get('host');
        $port   = (int) $request->get('port');

        $actualScheme = $request->getScheme();
        $actualHost   = $request->getHost();
        $actualPort   = (int) $request->getPort();

        $valid = $actualScheme === $scheme && $actualHost === $host && $actualPort === $port;

        return $this->createJsonResponse([
            'valid' => $valid,
        ]);
    }
}

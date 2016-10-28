<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Security\Handler;

use Application\DeskPRO\EntityRepository\Person as PersonRepository;
use DeskPRO\Bundle\AppBundle\AntiAbuse\AntiAbuse;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\LoginAbuseCheck;
use DeskPRO\Bundle\PortalBundle\EmailSender\PortalEmailSender;
use Doctrine\DBAL\Driver\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * When a login fails, this class does logging, checks, etc, and then redirects the user.
 */
class AuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{
    /**
     * @var PortalEmailSender
     */
    private $portal_mailer;

    /**
     * @var Connection
     */
    private $db;

    /**
     * @var PersonRepository
     */
    private $person_repo;

    /**
     * @var AntiAbuse
     */
    private $anti_abuse;

    public function __construct(HttpKernelInterface $httpKernel, HttpUtils $httpUtils, array $options, LoggerInterface $logger = null, PortalEmailSender $portal_mailer, Connection $db, PersonRepository $person_repo, AntiAbuse $anti_abuse)
    {
        parent::__construct($httpKernel, $httpUtils, $options, $logger);
        $this->portal_mailer = $portal_mailer;
        $this->db            = $db;
        $this->person_repo   = $person_repo;
        $this->anti_abuse    = $anti_abuse;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {

        // run failed login routine from old controller

        $attempt_person = null;
        if ($token = $exception->getToken()) {
            // Send alert
            if (!$attempt_person = $this->person_repo->findOneByEmail($token->getUsername())) {
                $attempt_person = $this->person_repo->findOneByEmail($token->getUser());
            }
            if (!$exception instanceof DisabledException && $attempt_person && $attempt_person->getPref('agent_notif.login_attempt_fail.email') && !$attempt_person->is_deleted) {
                $this->portal_mailer->sendLoginAlert($attempt_person, $request, false);
            }
        }

        // Save login log
        $ip = $request->getClientIp();
        if ($attempt_person) {
            $this->db->insert('login_log', [
                'person_id'    => $attempt_person->getId(),
                'area'         => defined('DP_INTERFACE') ? DP_INTERFACE : 'unknown',
                'is_success'   => 0,
                'ip_address'   => $ip,
                'hostname'     => @gethostbyaddr($ip) ?: '',
                'user_agent'   => empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'],
                'date_created' => date('Y-m-d H:i:s'),
            ]);
        }

        $check = new LoginAbuseCheck($attempt_person, $ip);
        $check->markAsCheckOnly();
        $this->anti_abuse->check($check);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'success' => false,
                    'captcha' => $check->isCaptchaRecommended(),
                    'reason'  => $exception instanceof AuthenticationException ? $exception->getMessageKey() : null,
                ]
            );
        }

        return parent::onAuthenticationFailure($request, $exception);
    }
}

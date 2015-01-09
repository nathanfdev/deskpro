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
 * @subpackage
 */

namespace Application\AuthBundle\Handler;

use Application\AppBundle\Mailer\NewMailer;
use Application\DeskPRO\EntityRepository\Person as PersonRepository;
use Doctrine\DBAL\Driver\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\Security\Http\HttpUtils;

class AuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{
    /**
     * @var NewMailer
     */
    private $new_mailer;

    /**
     * @var Connection
     */
    private $db;

    /**
     * @var PersonRepository
     */
    private $person_repo;

    public function __construct(HttpKernelInterface $httpKernel, HttpUtils $httpUtils, array $options = array(), LoggerInterface $logger = null, NewMailer $new_mailer, Connection $db, PersonRepository $person_repo)
    {
        parent::__construct($httpKernel, $httpUtils, $options, $logger);
        $this->new_mailer = $new_mailer;
        $this->db = $db;
        $this->person_repo = $person_repo;
    }


    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        //
        // run failed login routine from old controller
        //

        $token = $exception->getToken();

        // Send alert
        $attempt_person = $this->person_repo->findOneByEmail($token->getUsername());
        if ($attempt_person && $attempt_person->getPref('agent_notif.login_attempt_fail.email') && !$attempt_person->is_deleted) {
            $this->new_mailer->sendLoginAlert($attempt_person, false);
        }

        // Save login log
        if ($attempt_person) {
            $this->db->insert('login_log', array(
                'person_id' => $attempt_person->getId(),
                'area' => defined('DP_INTERFACE') ? DP_INTERFACE : 'unknown',
                'is_success' => 0,
                'ip_address' => dp_get_user_ip_address(),
                'hostname' => @gethostbyaddr(dp_get_user_ip_address()) ?: '',
                'user_agent' => empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'],
                'date_created' => date('Y-m-d H:i:s')
            ));
        }

        return parent::onAuthenticationFailure($request, $exception);
    }

}

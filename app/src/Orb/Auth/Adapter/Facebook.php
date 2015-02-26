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
 * Orb
 *
 * @package Orb
 * @category Auth
 */

namespace Orb\Auth\Adapter;

use Application\DeskPRO\Log\Logger;
use Orb\Auth\Result;
use Orb\Auth\StateHandler\StateHandlerInterface;
use Orb\Util\Arrays;

/**
 * Requirements:
 * - Facebook SDK: https://github.com/facebook/php-sdk
 */
class Facebook extends AbstractCallbackAdatper implements DisplayContextInterface
{
    /** @var string */
    protected $app_id;
    /** @var string */
    protected $app_secret;
    /** @var string */
    protected $display = 'page';

    /**
     * The facebook object
     * @var Facebook
     */
    protected $fb;

    /**
     * @param string $app_id     Your Facebook app id
     * @param string $app_secret Your facebook app secret
     */
    public function __construct($app_id, $app_secret)
    {
        $this->app_id = $app_id;
        $this->app_secret = $app_secret;
    }


    /**
     * Sets the display context: page or popup
     *
     * @param $context
     * @throws \InvalidArgumentException
     */
    public function setDisplayContext($context)
    {
        $context = strtolower($context);
        if (!in_array($context, array('page', 'popup'))) {
            throw new \InvalidArgumentException("Invalid display context `$context`");
        }

        $this->display = $context;
    }


    /**
     * Initialize the auth process by setting state, and returning a redirect result.
     *
     * @return \Orb\Auth\Result
     */
    protected function authenticateInitialize(StateHandlerInterface $state)
    {
        $this->fb = new \Facebook(
            array(
                'appId'  => $this->app_id,
                'secret' => $this->app_secret
            ),
            $state
        );

        // Gets a userid or false if no user logged in
        $user = $this->fb->getUser();

        $me = false;
        if ($user) {
            try {
                $me = $this->fb->api('/me');
            } catch (\FacebookApiException $e) {
                if ($this->logger) {
                    $this->logger->log(
                        "Exception: {$e->getCode()} {$e->getMessage()}\n{$e->getTraceAsString()}", Logger::ERR
                    );
                }
            }
        }

        // Already a user
        if ($me) {

            if ($this->logger) {
                $this->logger->log(
                    "No need to redirect, user is already logged in: \n" . trim(
                        Arrays::implodeTemplate($me, "{KEY}: {VAL}\n")
                    ),
                    Logger::DEBUG
                );
            }

            return $this->_meToResult($me);
        }

        $redirect_url = $this->fb->getLoginUrl(array(
            'redirect_uri' => $this->getCallbackUrl(),
            'display' => $this->display,
            'req_perms' => 'user_about_me,user_birthday,user_website,email',
        ));

        if ($this->logger) {
            $this->logger->log(
                "Redirecting to: $redirect_url",
                Logger::DEBUG
            );
        }

        return new Result(Result::REQUIRES_REDIRECT, null, array(Result::MSG_REDIRECT => $redirect_url));
    }



    /**
     * Process the callback and return a final result.
     *
     * @return \Orb\Auth\Result
     */
    protected function authenticateCallback(array $callback_data, StateHandlerInterface $state)
    {
        $this->fb = new \Facebook(
            array(
                'appId'  => $this->app_id,
                'secret' => $this->app_secret
            ),
            $state
        );

        $session = $this->fb->getUser();

        $time_start = microtime(true);
        if ($this->logger) {
            $this->logger->log("START Facebook::authenticateCallback", Logger::DEBUG);
        }

        $me = false;
        if ($session) {
            try {
                $me = $this->fb->api('/me');
            } catch (\FacebookApiException $e) {
                if ($this->logger) {
                    $this->logger->log(
                        "Exception: {$e->getCode()} {$e->getMessage()}\n{$e->getTraceAsString()}", Logger::ERR
                    );
                }
            }
        }

        if (!$me) {
            if ($this->logger) {
                $this->logger->log("No active FB session found. Failing.", Logger::DEBUG);
            }

            return new Result(Result::FAILURE, null, array('error_code' => 'failed_session', 'error_message' => 'No active FB session'));
        }

        if ($this->logger) {
            $this->logger->log(
                "Facebook Success: \n" . trim(Arrays::implodeTemplate($me, "{KEY}: {VAL}\n")),
                Logger::DEBUG
            );
        }

        if ($this->logger) {
            $this->logger->log(
                sprintf("END Facebook::authenticateCallback (took %.4fs)", microtime(true) - $time_start), Logger::DEBUG
            );
        }

        return $this->_meToResult($me);
    }


    protected function _meToResult($me)
    {
        $identity = new \Orb\Auth\Identity($me['id'], $me);
        $identity->setFriendlyIdentity($identity['link']);
        $result = new Result(Result::SUCCESS, $identity);

        return $result;
    }
}

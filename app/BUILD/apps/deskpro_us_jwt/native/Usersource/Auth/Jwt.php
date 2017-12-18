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

namespace deskpro_us_jwt\Usersource\Auth;

use Firebase\JWT\JWT as BaseJWT;
use League\Url\Url;
use Orb\Auth\Adapter;
use Orb\Auth\Adapter\AbstractCallbackAdatper;
use Orb\Auth\Identity;
use Orb\Auth\Result;
use Orb\Auth\StateHandler\StateHandlerInterface;
use Orb\Log\Loggable;
use Orb\Log\Logger;
use Orb\Util\Arrays;

class Jwt extends AbstractCallbackAdatper implements Adapter\SsoCapableInterface, Adapter\IframeSsoInterface, Loggable
{
    /**
     * @var \Orb\Log\Logger
     */
    protected $logger;

    /**
     * @var \Orb\Util\OptionsArray
     */
    protected $options;

    /**
     * @var string the single sign-off url
     */
    protected $logout_url;

    public function __construct(array $options)
    {
        $this->initOptions();
        $this->options->setArray($options);
    }

    protected function initOptions()
    {
        $this->options = new \Orb\Util\OptionsArray(
            [
                'url'               => '',
                'secret'            => '',
                'algo'              => 'HS256',
                'login_custom_text' => 'Login (JWT)',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function authenticateCallback(array $callback_data, StateHandlerInterface $state)
    {
        if ($this->logger) {
            $this->logger->log(
                'Attempting JWT Callback', Logger::DEBUG
            );
        }
        try {
            return $this->tryJwtAuth($callback_data);
        } catch (\Exception $e) {
            return new Result(
                Result::FAILURE_EXCEPTION, null,
                ['error_code' => 'exception', 'error_message' => 'An exception occurred', 'exception' => $e]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSsoLoginActionResult(\Application\DeskPRO\Controller\AbstractController $controller = null)
    {
        if ($this->logger) {
            $this->logger->log(
                'Attempting SSO Action', Logger::DEBUG
            );
        }
        try {
            return $this->tryJwtAuth($_REQUEST);
        } catch (\Exception $e) {
            return new Result(
                Result::FAILURE_EXCEPTION, null,
                ['error_code' => 'exception', 'error_message' => 'An exception occurred', 'exception' => $e]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function authenticateInitialize(StateHandlerInterface $state)
    {
        try {
            $redirect = $this->getFullRedirectUrl();

            if ($this->logger) {
                $this->logger->log(
                    'Initializing Callback Authentication', Logger::DEBUG
                );
                $this->logger->log(
                    "Redirecting to: $redirect", Logger::DEBUG
                );
            }

            // return a success result if we detect they are already logged in
            $result = new Result(Result::REQUIRES_REDIRECT, null, [Result::MSG_REDIRECT => $redirect]);

            return $result;
        } catch (\Exception $e) {
            return new Result(
                Result::FAILURE_EXCEPTION, null,
                ['error_code' => 'exception', 'error_message' => 'An exception occurred', 'exception' => $e]
            );
        }
    }

    /**
     * URL we send the deskpro user to after they log out of our system
     * This is to comply with sing sign-off in SAML and our JWT system, but is useful in any SSO implementation.
     *
     * @return string
     */
    public function getLogoutRedirectUrl()
    {
        return $this->logout_url ?: '';
    }

    /**
     * Allow external processes to determine and set the logout URL if needed. Should override any internal logic for
     * logout URL.
     */
    public function setLogoutRedirectUrl($url)
    {
        $this->logout_url = $url;
    }

    /**
     * {@inheritdoc}
     */
    public function getIframeTemplateParams($is_first_page_load)
    {
        return [
            'iframe_url' => $this->getFullRedirectUrl(),
            'render'     => true,
        ];
    }

    /**
     * @param array $callback_data
     *
     * @return Result
     */
    protected function tryJwtAuth(array $callback_data)
    {
        $time_start = microtime(true);
        if ($this->logger) {
            $this->logger->log('START Jwt::tryJwtAuth', Logger::DEBUG);
        }

        try {
            if (empty($callback_data['jwt'])) {
                throw new \InvalidArgumentException('Missing `jwt` (token) in callback data');
            }

            $jwt             = $callback_data['jwt'];
            $secret          = $this->options->get('secret');
            BaseJWT::$leeway = 60 * 15; // give 15 minutes of "leeway" around the token expiration
            $payload         = BaseJWT::decode($jwt, $secret, [$this->options->get('algo', 'HS256')]);
            $payload_array   = Arrays::fromStdClass($payload);

            if (empty($payload_array['email'])) {
                throw new \InvalidArgumentException('Missing required `email` in payload data');
            }

            if ($this->logger) {
                $op['jwt']    = $jwt;
                $op['secret'] = $secret;
                $this->logger->log(
                    "Given JWT (Token): $jwt", Logger::DEBUG
                );
                $this->logger->log(
                    "Decoding with secret: $secret", Logger::DEBUG
                );
                $this->logger->log(
                    "Payload contents: \n".trim(Arrays::implodeTemplate($payload_array, "{KEY}: {VAL}\n")), Logger::DEBUG
                );
                $this->logger->log(
                    'Identity: '.$payload_array['id'], Logger::DEBUG
                );
            }

            $identity = new Identity($payload_array['id'], $payload_array);
            $identity->setFriendlyIdentity($payload_array['email']);
            $result = new Result(Result::SUCCESS, $identity);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log(
                    "Exception: {$e->getCode()} {$e->getMessage()}\n{$e->getTraceAsString()}", Logger::ERR
                );
            }
            $exception_messages = [Result::MSG_EXCEPTION => $e];
            if ($e->getMessage() === 'Algorithm not allowed') {
                $exception_messages['display_errors'] = 'You selected the decryption algorithm "'.$this->options->get('algo').'" but the incoming JWT token used a different algorithm. Please re-check the selected algorithm.';
            }
            $result = new Result(Result::FAILURE_EXCEPTION, null, $exception_messages);
        }

        if ($this->logger) {
            $this->logger->log(
                sprintf('END Jwt::tryJwtAuth (took %.4fs)', microtime(true) - $time_start), Logger::DEBUG
            );
        }

        return $result;
    }

    /**
     * @return string
     */
    protected function getFullRedirectUrl()
    {
        $url = Url::createFromUrl($this->options->get('url'));
        $url->getQuery()->modify(['return' => $this->getCallbackUrl()]);
        $redirect = (string) $url;

        return $redirect;
    }

    /**
     * {@inheritdoc}
     */
    public function isBackgroundSsoSimpleRefresh()
    {
        return true;
    }
}

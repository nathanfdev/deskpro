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

namespace deskpro_us_jwt\Usersource\Auth;

use Application\DeskPRO\App;
use League\Url\Url;
use Orb\Auth\Adapter;
use Orb\Auth\Adapter\AbstractCallbackAdatper;
use Orb\Auth\Identity;
use Orb\Auth\Result;
use Orb\Auth\StateHandler\StateHandlerInterface;
use Orb\Log\Logger;
use Orb\Util\Arrays;

class Jwt extends AbstractCallbackAdatper implements Adapter\SsoCapableInterface, Adapter\IframeSsoInterface
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
			array(
				'url'               => '',
				'secret'            => '',
				'login_custom_text' => 'Login (JWT)'
			)
		);
	}


	/**
	 * {@inheritdoc}
	 */
	protected function authenticateCallback(array $callback_data, StateHandlerInterface $state)
	{
		if ($this->logger) {
			$this->logger->log(
				"Attempting JWT Callback", Logger::DEBUG
			);
		}
        return $this->tryJwtAuth($callback_data);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getSsoLoginActionResult(\Application\DeskPRO\Controller\AbstractController $controller)
	{
		if ($this->logger) {
			$this->logger->log(
				"Attempting SSO Action", Logger::DEBUG
			);
		}
		return $this->tryJwtAuth($_REQUEST);
	}


	/**
	 * {@inheritdoc}
	 */
	protected function authenticateInitialize(StateHandlerInterface $state)
	{
        $redirect = $this->getFullRedirectUrl();

		if ($this->logger) {
			$this->logger->log(
				"Initializing Callback Authentication", Logger::DEBUG
			);
			$this->logger->log(
				"Redirecting to: $redirect", Logger::DEBUG
			);
		}

		// return a success result if we detect they are already logged in
		$result = new Result(Result::REQUIRES_REDIRECT, null, array(Result::MSG_REDIRECT => $redirect));

		return $result;
	}

	/**
	 * URL we send the deskpro user to after they log out of our system
	 * This is to comply with sing sign-off in SAML and our JWT system, but is useful in any SSO implementation
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
     * {@inheritDoc}
     */
    public function getIframeTemplateParams($is_first_page_load)
    {
        return array(
            'iframe_url' => $this->getFullRedirectUrl(),
	        'render' => true
        );
    }


    /**
     * @param array $callback_data
     * @return Result
     */
    protected function tryJwtAuth(array $callback_data)
    {
	    $time_start = microtime(true);
	    if ($this->logger) {
		    $this->logger->log("START Jwt::tryJwtAuth", Logger::DEBUG);
	    }

        try {
            $jwt           = $callback_data['jwt'];
            $secret        = $this->options->get('secret');
            $payload       = \JWT::decode($jwt, $secret, true);
            $payload_array = Arrays::fromStdClass($payload);

	        if ($this->logger) {
		        $op['jwt'] = $jwt;
		        $op['secret'] = $secret;
		        $this->logger->log(
			        "Given JWT (Token): $jwt", Logger::DEBUG
		        );
		        $this->logger->log(
			        "Decoding with secret: $secret", Logger::DEBUG
		        );
		        $this->logger->log(
			        "Payload contents: \n" . trim(Arrays::implodeTemplate($payload_array, "{KEY}: {VAL}\n")), Logger::DEBUG
		        );
		        $this->logger->log(
			        "Identity: " . $payload_array['id'], Logger::DEBUG
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
            $result = new Result(Result::FAILURE_EXCEPTION, null, array(Result::MSG_EXCEPTION => $e));
        }

	   if ($this->logger) {
		   $this->logger->log(
			   sprintf("END Jwt::tryJwtAuth (took %.4fs)", microtime(true) - $time_start), Logger::DEBUG
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
        $url->getQuery()->modify(array('return' => $this->getCallbackUrl()));
        $redirect = (string) $url;

        return $redirect;
    }


	/**
	 * {@inheritDoc}
	 */
	public function isBackgroundSsoSimpleRefresh()
	{
		return true;
	}
}

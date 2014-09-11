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
 * Orb
 *
 * @package Orb
 * @category Auth
 */

namespace Orb\Auth\Adapter;

use Application\DeskPRO\App;
use League\Url\Url;
use Orb\Auth\Adapter;
use Orb\Auth\Identity;
use Orb\Auth\Result;
use Orb\Auth\StateHandler\StateHandlerInterface;
use Orb\Util\Arrays;

class Saml extends AbstractCallbackAdatper implements Adapter\SsoCapableInterface, Adapter\IframeSsoInterface, SamlAdapterInterface
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
	 * @var \OneLogin_Saml2_Auth
	 */
	protected $saml;

	/**
	 * @var string url to metadata of this adapter
	 */
	protected $metadata_xml_url;

	/**
	 * @var string url single logout service url for this adapter
	 */
	protected $sls_url;

	/**
	 * @var string
	 */
	protected $logout_url;


	public function __construct(array $options)
	{
		$this->logout_url = ''; // make sure you have a sso url made that this will always goto on logout look at ssocapable interface
		$this->initOptions();
		$this->options->setArray($options);
	}

	/**
	 * Only create this directly before using it, as we need the callback URL to be set first.
	 *
	 * @return \OneLogin_Saml2_Auth
	 */
	protected function createSamlProcessor()
	{
		$options = $this->options;
		$saml    = new \OneLogin_Saml2_Auth(
			$settings = array(
				'sp'  => array(
					'entityId'                 => $this->getMetadataXmlUrl(),
					'assertionConsumerService' => array(
						'url' => $this->getCallbackUrl(),
					),
					'singleLogoutService'      => array(
						'url' => $this->getSingleLogoutServiceUrl(),
					),
					// enforce a persistent ID for person association
					'NameIDFormat'             => \OneLogin_Saml2_Constants::NAMEID_PERSISTENT,
				),
				'idp' => array(
					'entityId'            => $options['issuer_id'],
					'singleSignOnService' => array(
						'url' => $options['sso_url'],
					),
					'singleLogoutService' => array(
						'url' => $options['slo_url'],
					),
					'certFingerprint'     => $options['cert_fingerprint'],
				)
			)
		);
		$saml->setStrict(false);

		return $saml;
	}


	protected function initOptions()
	{
		$this->options = new \Orb\Util\OptionsArray(
			array(
				'sso_url' => '',
				'slo_url' => '',
				'cert_fingerprint' => '',
				'login_custom_text' => ''
			)
		);
	}


	/**
	 * Process the callback and return a final result.
	 *
	 * @return \Orb\Auth\Result
	 */
	protected function authenticateCallback(array $callback_data, StateHandlerInterface $state)
	{
		return $this->processAcs($callback_data);
	}


	public function getSsoLoginActionResult(\Application\DeskPRO\Controller\AbstractController $controller)
	{
		return $this->processAcs($_REQUEST);
	}


	/**
	 * Initialize the auth process by forwarding the user to the IdP to start the process.
	 *
	 * @return \Orb\Auth\Result
	 */
	protected function authenticateInitialize(StateHandlerInterface $state)
	{
		$saml = $this->createSamlProcessor();
		$saml->login();
	}


	/**
	 * This is executed when the SAML IdP POSTS back to us after we requested authentication
	 *
	 * @param array $callback_data
	 * @return Result
	 */
	protected function processAcs(array $callback_data)
	{
		$saml = $this->createSamlProcessor();
		$saml->processResponse();

		$errors = $saml->getErrors();

		if (!(empty($errors) && $saml->isAuthenticated())) {
			return new Result(Result::FAILURE, null, array('saml_errors' => $errors));
		}

		$attrs = $saml->getAttributes();
		$user_info = array();

		$user_info['email'] = Arrays::reachForFirstValueInKey($attrs, 'email');
		$user_info['first_name'] = Arrays::reachForFirstValueInKey($attrs, 'first_name');
		$user_info['last_name'] = Arrays::reachForFirstValueInKey($attrs, 'last_name');
		$user_info['name'] = Arrays::reachForFirstValueInKey($attrs, 'name');


		$id = new Identity($saml->getNameId(), $user_info);

		return new Result(Result::SUCCESS, $id);
	}


	/**
	 * URL we send the deskpro user to after they log out of our system
	 * This is to comply with sing sign-off in SAML and our JWT system, but is useful in any SSO implementation
	 *
	 * @return string
	 */
	public function getLogoutRedirectUrl()
	{
		if (!$this->logout_url) {
			throw new \RuntimeException('no logout url defined for this SSO adapter in this context');
		}

		return $this->logout_url;
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
		// wrong. you need to set the iframe to goto the same url as the normal authenticate url, except it has
		// to use the sso callback. sent us there with a "saml" GET param!
		return array(
			'iframe_url' => $this->getCallbackUrl(),
			'render'     => true
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function isBackgroundSsoSimpleRefresh()
	{
		return true;
	}


	public function setMetadataXmlUrl($url)
	{
		$this->metadata_xml_url = $url;
	}


	public function getMetadataXmlUrl()
	{
		return $this->metadata_xml_url;
	}


	public function setSingleLogoutServiceUrl($url)
	{
		$this->sls_url = $url;
	}


	public function getSingleLogoutServiceUrl()
	{
		return $this->sls_url;
	}
}

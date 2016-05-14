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
 * Orb.
 *
 * @category Auth
 */
namespace Orb\Auth\Adapter;

use Application\DeskPRO\Saml\SamlMetadataBuilder;
use Orb\Auth\Identity;
use Orb\Auth\Result;
use Orb\Auth\StateHandler\StateHandlerInterface;
use Orb\Log\Logger;
use Orb\Util\Arrays;
use Symfony\Component\HttpFoundation\Response;

class Saml extends AbstractCallbackAdatper implements SsoCapableInterface, IframeSsoInterface, SamlAdapterInterface, ExtraDetailsInterface
{
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
     * @var string not required, but can be a backup if no saml redirect is provided
     */
    protected $backupLogoutUrl;

    public function __construct(array $options)
    {
        $this->initOptions();
        $this->options->setArray($options);
    }

    protected function initOptions()
    {
        $this->options = new \Orb\Util\OptionsArray(
            array(
                'sso_url'           => '',
                'slo_url'           => '',
                'cert_fingerprint'  => '',
                'name_id_format'    => '',
                'login_custom_text' => '',
            )
        );
    }

    /**
     * @return array
     */
    protected function getSamlSettings()
    {
        $settings = array(
            'sp' => array(
                'entityId'                 => $this->getMetadataXmlUrl(),
                'assertionConsumerService' => array(
                    'url' => $this->getCallbackUrl(),
                ),
                'singleLogoutService' => array(
                    'url' => $this->getSingleLogoutServiceUrl(),
                ),
                'NameIDFormat' => $this->options['name_id_format'] ?: \OneLogin_Saml2_Constants::NAMEID_PERSISTENT,
            ),
            'idp' => array(
                'entityId'            => $this->options['issuer_id'],
                'singleSignOnService' => array(
                    'url' => $this->options['sso_url'],
                ),
                'singleLogoutService' => array(
                    'url' => $this->options['slo_url'],
                ),
                'x509cert'        => $this->options['cert'] ?: null,
                'certFingerprint' => $this->options['cert_fingerprint'] ?: null,
            ),
        );

        if ($this->options['sign_authn_request']) {
            $settings['security'] = array(
                'authnRequestsSigned' => true,
            );

            $settings['sp']['privateKey'] = $this->options['sp_private_key'];
            $settings['sp']['x509cert']   = $this->options['sp_public_x509'];
        }

        return $settings;
    }

    /**
     * {@inheritdoc}
     */
    protected function authenticateCallback(array $callback_data, StateHandlerInterface $state)
    {
        if ($this->logger) {
            $this->logger->log(
                'Attempting SAML Callback', Logger::DEBUG
            );
            $this->logger->log(
                "Using SAML settings: \n".trim(Arrays::implodeTemplate($this->getSamlSettings(), "{KEY}: {VAL}\n")),
                Logger::DEBUG
            );
        }
        try {
            return $this->processAcs($callback_data);
        } catch (\Exception $e) {
            return new Result(
                Result::FAILURE_EXCEPTION, null,
                array('error_code' => 'exception', 'error_message' => 'An exception occurred', 'exception' => $e)
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
                'Attampting SAML SSO Result', Logger::DEBUG
            );
            $this->logger->log(
                "Using SAML settings: \n".trim(Arrays::implodeTemplate($this->getSamlSettings(), "{KEY}: {VAL}\n")),
                Logger::DEBUG
            );
        }
        try {
            return $this->processAcs($_REQUEST);
        } catch (\Exception $e) {
            return new Result(
                Result::FAILURE_EXCEPTION, null,
                array('error_code' => 'exception', 'error_message' => 'An exception occurred', 'exception' => $e)
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function authenticateInitialize(StateHandlerInterface $state)
    {
        try {
            $saml = $this->createSamlProcessor();
            if ($this->logger) {
                $this->logger->log(
                    'Initializing SAML Authentication', Logger::DEBUG
                );
                $this->logger->log(
                    "Using SAML settings: \n".trim(Arrays::implodeTemplate($this->getSamlSettings(), "{KEY}: {VAL}\n")),
                    Logger::DEBUG
                );
            }
            $saml->login();
        } catch (\Exception $e) {
            return new Result(
                Result::FAILURE_EXCEPTION, null,
                array('error_code' => 'exception', 'error_message' => 'An exception occurred', 'exception' => $e)
            );
        }
    }

    /**
     * This is executed when the SAML IdP POSTS back to us after we requested authentication.
     *
     * @param array $callback_data
     *
     * @return Result
     */
    protected function processAcs(array $callback_data)
    {
        $time_start = microtime(true);
        if ($this->logger) {
            $this->logger->log('START Saml::processAcs', Logger::DEBUG);
            $this->logger->log(
                "SAML Settings: \n".trim(Arrays::implodeTemplate($this->getSamlSettings(), "{KEY}: {VAL}\n")),
                Logger::DEBUG
            );
        }

        try {
            $saml = $this->createSamlProcessor();
            $saml->processResponse();
        } catch (\Exception $e) {
            return new Result(
                Result::FAILURE_EXCEPTION, null,
                array('error_code' => 'exception', 'error_message' => 'An exception occurred', 'exception' => $e)
            );
        }

        $errors = $saml->getErrors();

        if (!(empty($errors) && $saml->isAuthenticated())) {
            if ($this->logger) {
                $this->logger->log(
                    "SAML Errors: \n".trim(Arrays::implodeTemplate($errors, "{KEY}: {VAL}\n")),
                    Logger::DEBUG
                );
                if ($saml->getLastErrorReason()) {
                    $this->logger->log(
                        'Last Error Reason: '.$saml->getLastErrorReason(),
                        Logger::DEBUG
                    );
                }
            }

            return new Result(Result::FAILURE, null, array('saml_errors' => $errors));
        }

        $attrs = $saml->getAttributes();

        if ($this->logger) {
            $this->logger->log(
                "SAML Returned Attributes: \n".trim(Arrays::implodeTemplate($attrs, "{KEY}: {VAL}\n")),
                Logger::DEBUG
            );
        }

        // start user_info as the $attrs from the saml response so that people can filter on them
        if (is_array($attrs)) {
            $user_info = $attrs;
        } else {
            $user_info = array();
        }

        $user_info['email']      = Arrays::reachForFirstValueInKey($attrs, 'email');
        $user_info['first_name'] = Arrays::reachForFirstValueInKey($attrs, 'first_name');
        $user_info['last_name']  = Arrays::reachForFirstValueInKey($attrs, 'last_name');
        $user_info['name']       = Arrays::reachForFirstValueInKey($attrs, 'name');

        // add some stuff for xmlsoap (Azure AD)
        if (!$user_info['email']) {
            $user_info['email'] = Arrays::reachForFirstValueInKey($attrs, 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress');
        }
        if (!$user_info['first_name']) {
            $user_info['first_name'] = Arrays::reachForFirstValueInKey($attrs, 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname');
        }
        if (!$user_info['last_name']) {
            $user_info['last_name'] = Arrays::reachForFirstValueInKey($attrs, 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/surname');
        }
        // end xmlsoap stuff

        $id = new Identity($saml->getNameId(), $user_info);

        if ($this->logger) {
            $user_info_extra             = $user_info;
            $user_info_extra['identity'] = $id->getIdentity();
            $this->logger->log(
                "SAML Success: \n".trim(Arrays::implodeTemplate($user_info_extra, "{KEY}: {VAL}\n")),
                Logger::DEBUG
            );
        }

        if ($this->logger) {
            $this->logger->log(
                sprintf('END Saml::processAcs (took %.4fs)', microtime(true) - $time_start), Logger::DEBUG
            );
        }

        return new Result(Result::SUCCESS, $id);
    }

    /**
     * URL we send the deskpro user to after they log out of our system
     * This is to comply with sing sign-off in SAML and our JWT system, but is useful in any SSO implementation.
     */
    public function getLogoutRedirectUrl()
    {
        $saml          = $this->createSamlProcessor();
        $saml_settings = $saml->getSettings();
        $idpData       = $saml_settings->getIdPData();
        if (isset($idpData['singleLogoutService']) && isset($idpData['singleLogoutService']['url'])) {
            $sloUrl = $idpData['singleLogoutService']['url'];
        } else {
            throw new \Exception('The IdP does not support Single Log Out');
        }

        $logoutRequest = new \OneLogin_Saml2_LogoutRequest($saml_settings);
        $samlRequest   = $logoutRequest->getRequest();
        $parameters    = array('SAMLRequest' => $samlRequest);
        $url           = \OneLogin_Saml2_Utils::redirect($sloUrl, $parameters, true);

        return trim($url) ?: $this->backupLogoutUrl;
    }

    /**
     * Allow external processes to determine and set the logout URL if needed. Should override any internal logic for
     * logout URL.
     */
    public function setLogoutRedirectUrl($url)
    {
        $this->backupLogoutUrl = $url;
    }

    /**
     * {@inheritdoc}
     */
    public function getIframeTemplateParams($is_first_page_load)
    {
        return array(
            'iframe_url' => $this->getCallbackUrl(),
            'render'     => true,
        );
    }

    /**
     * {@inheritdoc}
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

    /**
     * Return a response OR do the redirect yourself inside the method.
     *
     * @return \Application\Deskpro\HttpFoundation\Request
     */
    public function performSingleLogOutService()
    {
        $saml = $this->createSamlProcessor();
        $saml->logout();
    }

    public function setSingleLogoutServiceUrl($url)
    {
        $this->sls_url = $url;
    }

    public function getSingleLogoutServiceUrl()
    {
        return $this->sls_url;
    }

    /**
     * Only create this directly before using it, as we need the callback URL to be set first.
     *
     * @return \OneLogin_Saml2_Auth
     */
    protected function createSamlProcessor()
    {
        $saml = new \OneLogin_Saml2_Auth(
            $this->getSamlSettings()
        );
        $saml->setStrict(false);

        return $saml;
    }

    /**
     * Return a response to send to browser.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getMetadataXmlResponse()
    {
        $saml_metadata = $this->getMetadataXml();

        $response = new Response($saml_metadata, 200);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    public function getMetadataXml()
    {
        $saml = $this->createSamlProcessor();
        $sp   = $saml->getSettings()->getSPData();

        $custom_xml = '';
        if ($this->options->get('include_custom_metadata_xml')) {
            $custom_xml = $this->options->get('custom_metadata_xml');
        }

        return SamlMetadataBuilder::builder($sp, false, false, null, null, array(), array(), array(), $custom_xml);
    }

    /**
     * @return array
     */
    public function getExtraDetails()
    {
        try {
            $xml_text = $this->getMetadataXml();
        } catch (\Exception $e) {
            $xml_text = 'SP Metadata not yet available. Please fill out SSO URL and Issuer Metadata (a.k.a IdP EntityID) settings and save.';
        }

        return array(
            'consumer_url'  => $this->getCallbackUrl(),
            'metadata_url'  => $this->getMetadataXmlUrl(),
            'metadata_text' => $xml_text,
            'slo_url'       => $this->getSingleLogoutServiceUrl(),
        );
    }
}

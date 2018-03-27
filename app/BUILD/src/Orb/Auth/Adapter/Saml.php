<?php

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
use Orb\Validator\StringEmail;
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
    protected $metadataXmlUrl;

    /**
     * @var string url single logout service url for this adapter
     */
    protected $slsUrl;

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
            [
                'sso_url'           => '',
                'slo_url'           => '',
                'cert_fingerprint'  => '',
                'name_id_format'    => '',
                'login_custom_text' => '',
            ]
        );
    }

    /**
     * @return array
     */
    protected function getSamlSettings()
    {
        $settings = [
            'security' => [
                'requestedAuthnContext' => false,
            ],
            'sp' => [
                'entityId'                 => $this->getMetadataXmlUrl(),
                'assertionConsumerService' => [
                    'url' => $this->getCallbackUrl(),
                ],
                'singleLogoutService' => [
                    'url' => $this->getSingleLogoutServiceUrl(),
                ],
                'NameIDFormat' => $this->options['name_id_format'] ?: \OneLogin_Saml2_Constants::NAMEID_PERSISTENT,
            ],
            'idp' => [
                'entityId'            => $this->options['issuer_id'],
                'singleSignOnService' => [
                    'url' => $this->options['sso_url'],
                ],
                'singleLogoutService' => [
                    'url' => $this->options['slo_url'],
                ],
                'x509cert'        => $this->options['cert'] ?: null,
                'certFingerprint' => $this->options['cert_fingerprint'] ?: null,
            ],
        ];

        if ($this->options['sign_authn_request']) {
            $settings['security']['authnRequestsSigned'] = true;

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
            if ($this->logger) {
                $this->logger->log(
                    $e->getMessage(),
                    Logger::DEBUG
                );
            }

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
                ['error_code' => 'exception', 'error_message' => 'An exception occurred', 'exception' => $e]
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
        $timeStart = microtime(true);
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
            if ($this->logger) {
                $this->logger->log($e->getMessage(), Logger::DEBUG);
            }

            return new Result(
                Result::FAILURE_EXCEPTION, null,
                ['error_code' => 'exception', 'error_message' => 'An exception occurred', 'exception' => $e]
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

            return new Result(Result::FAILURE, null, ['saml_errors' => $errors]);
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
            $userInfo = $attrs;
        } else {
            $userInfo = [];
        }

        $userInfo['email']      = Arrays::reachForFirstValueInKey($attrs, 'email');
        $userInfo['first_name'] = Arrays::reachForFirstValueInKey($attrs, 'first_name');
        $userInfo['last_name']  = Arrays::reachForFirstValueInKey($attrs, 'last_name');
        $userInfo['name']       = Arrays::reachForFirstValueInKey($attrs, 'name');

        // add some stuff for xmlsoap (Azure AD)
        if (!$userInfo['email']) {
            $userInfo['email'] = Arrays::reachForFirstValueInKey($attrs, 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress');
        }
        if (!$userInfo['first_name']) {
            $userInfo['first_name'] = Arrays::reachForFirstValueInKey($attrs, 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname');
        }
        if (!$userInfo['last_name']) {
            $userInfo['last_name'] = Arrays::reachForFirstValueInKey($attrs, 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/surname');
        }
        // end xmlsoap stuff

        // Office 365 empty emailAddress workaround
        if (
            !$userInfo['email']
            && !empty($saml->getNameId())
            && StringEmail::isValueValid($saml->getNameId())
            && $saml->getNameIdFormat() === 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress'
        ) {
            $userInfo['email'] = $saml->getNameId();
        }

        $id = new Identity($saml->getNameId(), $userInfo);

        if ($this->logger) {
            $userInfoExtra             = $userInfo;
            $userInfoExtra['identity'] = $id->getIdentity();
            $this->logger->log(
                "SAML Success: \n".trim(Arrays::implodeTemplate($userInfoExtra, "{KEY}: {VAL}\n")),
                Logger::DEBUG
            );
        }

        if ($this->logger) {
            $this->logger->log(
                sprintf('END Saml::processAcs (took %.4fs)', microtime(true) - $timeStart), Logger::DEBUG
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
        $saml         = $this->createSamlProcessor();
        $samlSettings = $saml->getSettings();
        $idpData      = $samlSettings->getIdPData();
        if (isset($idpData['singleLogoutService']) && isset($idpData['singleLogoutService']['url'])) {
            $sloUrl = $idpData['singleLogoutService']['url'];
        } else {
            throw new \Exception('The IdP does not support Single Log Out');
        }

        $logoutRequest = new \OneLogin_Saml2_LogoutRequest($samlSettings);
        $samlRequest   = $logoutRequest->getRequest();
        $parameters    = ['SAMLRequest' => $samlRequest];
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
        return [
            'iframe_url' => $this->getCallbackUrl(),
            'render'     => true,
        ];
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
        $this->metadataXmlUrl = $url;
    }

    public function getMetadataXmlUrl()
    {
        return $this->metadataXmlUrl;
    }

    /**
     * Return a response OR do the redirect yourself inside the method.
     */
    public function performSingleLogOutService()
    {
        $saml = $this->createSamlProcessor();
        $saml->logout();
    }

    public function setSingleLogoutServiceUrl($url)
    {
        $this->slsUrl = $url;
    }

    public function getSingleLogoutServiceUrl()
    {
        return $this->slsUrl;
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
        $samlMetadata = $this->getMetadataXml();

        $response = new Response($samlMetadata, 200);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    public function getMetadataXml()
    {
        $saml = $this->createSamlProcessor();
        $sp   = $saml->getSettings()->getSPData();

        $customXml = '';
        if ($this->options->get('include_custom_metadata_xml')) {
            $customXml = $this->options->get('custom_metadata_xml');
        }

        return SamlMetadataBuilder::builder($sp, false, false, null, null, [], [], [], $customXml);
    }

    /**
     * @return array
     */
    public function getExtraDetails()
    {
        try {
            $xmlText = $this->getMetadataXml();
        } catch (\Exception $e) {
            $xmlText = 'SP Metadata not yet available. Please fill out SSO URL and Issuer Metadata (a.k.a IdP EntityID) settings and save.';
        }

        return [
            'consumer_url'  => $this->getCallbackUrl(),
            'metadata_url'  => $this->getMetadataXmlUrl(),
            'metadata_text' => $xmlText,
            'slo_url'       => $this->getSingleLogoutServiceUrl(),
        ];
    }
}

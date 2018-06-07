<?php

/**
 * Orb.
 *
 * @category Auth
 */

namespace Orb\Auth\Adapter;

use DpSys\LowError\SystemErrorHandler;
use Orb\Auth\Identity;
use Orb\Auth\Result;
use Orb\Log\Logger;
use Orb\Util\Arrays;

class LdapRaw extends AbstractLdapBasedAdapter implements FormLoginInterface
{
    const OPT_HOST            = 'host';
    const OPT_PORT            = 'port';
    const OPT_TLS             = 'useStartTls';
    const OPT_SSL             = 'useSsl';
    const OPT_BASE_DN         = 'baseDn';
    const OPT_LOOKUP_USERNAME = 'username';
    const OPT_LOOKUP_PASSWORD = 'password';

    const OPT_FIELD_ID       = 'field_id';
    const OPT_FIELD_EMAIL    = 'field_email';
    const OPT_FIELD_USERNAME = 'field_username';

    /**
     * Disable SSL certificate validation. Allow to use self-signed certificates.
     */
    const OPT_DISABLE_CERT_VALIDATION = 'disable_cert_validation';

    /**
     * @var \Orb\Log\Logger
     */
    protected $logger;

    /**
     * @var string
     */
    protected $set_username;
    /**
     * @var string
     */
    protected $set_password;

    /**
     * @var array
     */
    protected $options = [
        self::OPT_HOST                    => 'localhost',
        self::OPT_PORT                    => null, // null means default of 389 or 636 if ssl enabled
        self::OPT_TLS                     => false,
        self::OPT_SSL                     => false,
        self::OPT_BASE_DN                 => '',
        self::OPT_LOOKUP_USERNAME         => null,
        self::OPT_LOOKUP_PASSWORD         => null,
        self::OPT_FIELD_ID                => 'dn',
        self::OPT_FIELD_EMAIL             => 'mail',
        self::OPT_FIELD_USERNAME          => 'uid',
        self::OPT_DISABLE_CERT_VALIDATION => false,
        'accountCanonicalForm'            => 2,
        'bindRequiresDn'                  => true,
        'ldapClass'                       => null,
    ];

    public function __construct(array $options)
    {
        $this->options = array_merge($this->options, $options);
        if (!$this->options['field_email']) {
            $this->options['field_email'] = 'mail';
        }
        if (!$this->options['field_username']) {
            $this->options['field_username'] = 'uid';
        }

        if (!isset($this->options['accountFilterFormat']) || !$this->options['accountFilterFormat']) {
            $this->options['accountFilterFormat'] = '(|(dn=%1$s)(mail=%1$s)(uid=%1$s))';
        }
    }

    /**
     * @param string $username
     * @param string $password
     */
    public function setFormData(array $form_data)
    {
        $this->set_username = !empty($form_data['username']) ? (string) $form_data['username'] : '';
        $this->set_password = !empty($form_data['password']) ? (string) $form_data['password'] : '';
    }

    /**
     * @return \Zend\Authentication\Adapter\Ldap
     */
    public function getZendAuthAdapter()
    {
        if ($this->isSecure()
            && isset($this->options[self::OPT_DISABLE_CERT_VALIDATION]) && $this->options[self::OPT_DISABLE_CERT_VALIDATION]
        ) {
            putenv('LDAPTLS_REQCERT=never');
        }
        $options = ['tryUsernameSplit' => false];
        foreach (
            [
                'host',
                'port',
                'baseDn',
                'username',
                'password',
                'accountFilterFormat',
                'accountCanonicalForm',
                'bindRequiresDn',
                'useStartTls',
                'useSsl',
            ] as $k) {
            if (isset($this->options[$k]) && $this->options[$k]) {
                $options[$k] = $this->options[$k];
            }
        }

        $auth = new \Zend\Authentication\Adapter\Ldap([$options], $this->set_username, $this->set_password);

        if ($this->options['ldapClass']) {
            $class = $this->options['ldapClass'];
            $ldap  = new $class();
            $auth->setLdap($ldap);

            // setLdap resets options (why?!), so we need to reset it back
            $auth->setOptions([$options]);
        }

        return $auth;
    }

    /**
     * Authenticate a user.
     *
     * @return
     */
    public function doAuthenticate()
    {
        if (!$this->set_username) {
            return new Result(Result::FAILURE, null, ['error_code' => 'missing_input_username', 'error_message' => 'No username provided']);
        }
        if (!$this->set_password) {
            return new Result(Result::FAILURE, null, ['error_code' => 'missing_input_password', 'error_message' => 'No password provided']);
        }

        $time_start = microtime(true);
        if ($this->logger) {
            $this->logger->log('START Ldap::authenticate', Logger::DEBUG);
            $this->logger->log('Options: '.trim(print_r($this->options, 1)), Logger::DEBUG);
            $this->logger->log("Request: {$this->set_username}:{$this->set_password}", Logger::DEBUG);
        }

        $auth = $this->getZendAuthAdapter();

        try {
            /** @var $result \Zend\Authentication\Result */
            $result = $auth->authenticate();
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("Exception: {$e->getCode()} {$e->getMessage()}\n{$e->getTraceAsString()}", Logger::ERR);
            }

            return new Result(Result::FAILURE_EXCEPTION, null, ['error_code' => 'exception', 'error_message' => 'An exception occurred', 'exception' => $e]);
        }

        if ($this->logger) {
            foreach ($result->getMessages() as $msg) {
                $this->logger->log($msg, \Orb\Log\Logger::DEBUG);
            }

            $this->logger->log(sprintf('END Ldap::authenticate (took %.4fs)', microtime(true) - $time_start), Logger::DEBUG);
        }

        if (!$result->isValid()) {
            return new Result(Result::FAILURE_INVALID_CREDS, null, ['error_code' => 'invalid_credentials', 'error_message' => 'Invalid username or password']);
        }

        $raw_info                      = [];
        $raw_info['identity_friendly'] = $result->getIdentity();

        try {
            $zend_auth = $this->getZendAuthAdapter();
            // Bogus because zend only creates ldap obj when its needed,
            // so this is a hack to get it to set all the correct options
            // for us
            try {
                $zend_auth->setUsername('__bogus__');
                $zend_auth->setPassword('__bogus__');
                $zend_auth->authenticate();
            } catch (\Exception $e) {
            }

            /** @var $ldap \Zend\Ldap\Ldap */
            $ldap = $zend_auth->getLdap();

            $dn = $ldap->getCanonicalAccountName($result->getIdentity(), \Zend\Ldap\Ldap::ACCTNAME_FORM_DN);

            /** @var $rec \Zend\Ldap\Node */
            $rec = $ldap->getNode($dn);
            if ($rec) {
                $raw_info = array_merge($raw_info, $rec->getAttributes());

                if (!empty($raw_info['samaccountname'])) {
                    $raw_info['friendly_identity'] = Arrays::getFirstItem($raw_info['samaccountname']);
                } elseif (!empty($raw_info['uid'])) {
                    $raw_info['friendly_identity'] = Arrays::getFirstItem($raw_info['uid']);
                }
                if (!empty($raw_info['distinguishedname'])) {
                    $raw_info['identity'] = Arrays::getFirstItem($raw_info['distinguishedname']);
                } elseif (!empty($raw_info['dn'])) {
                    $raw_info['identity'] = Arrays::getFirstItem($raw_info['dn']);
                } else {
                    $raw_info['identity'] = $result->getIdentity();
                }

                if ($rec->getAttribute('givenName')) {
                    $raw_info['first_name'] = Arrays::getFirstItem($rec->getAttribute('givenName'));
                }
                if ($rec->getAttribute('SN')) {
                    $raw_info['last_name'] = Arrays::getFirstItem($rec->getAttribute('SN'));
                }

                if ($rec->getAttribute('givenName') && $rec->getAttribute('SN')) {
                    $raw_info['name'] = Arrays::getFirstItem($rec->getAttribute('givenName')).' '.Arrays::getFirstItem($rec->getAttribute('SN'));
                } elseif ($rec->getAttribute('name')) {
                    $raw_info['name'] = Arrays::getFirstItem($rec->getAttribute('name'));
                } elseif ($rec->getAttribute('CN')) {
                    $raw_info['name'] = Arrays::getFirstItem($rec->getAttribute('CN'));
                }

                if ($rec->getAttribute('mail')) {
                    $raw_info['email_address'] = Arrays::getFirstItem($rec->getAttribute('mail'));
                }

                if ($rec->getAttribute('jpegPhoto')) {
                    $raw_info['picture_data'] = Arrays::getFirstItem($rec->getAttribute('jpegPhoto'));
                } elseif ($rec->getAttribute('thumbnailPhoto')) {
                    $raw_info['picture_data'] = Arrays::getFirstItem($rec->getAttribute('thumbnailPhoto'));
                }
            }
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log("Exception: {$e->getCode()} {$e->getMessage()}\n{$e->getTraceAsString()}", Logger::ERR);
            }

            return new Result(Result::FAILURE_EXCEPTION, null, ['error_code' => 'exception', 'error_message' => 'An exception occurred', 'exception' => $e]);
        }

        $identity = new Identity($raw_info['identity'], $raw_info);

        return new Result(Result::SUCCESS, $identity);
    }

    /**
     * Authenticate a user.
     *
     * @return
     */
    public function getIdentityForDn($provided_dn)
    {
        try {
            $zend_auth = $this->getZendAuthAdapter();
            // Bogus because zend only creates ldap obj when its needed,
            // so this is a hack to get it to set all the correct options
            // for us
            try {
                $zend_auth->setUsername('__bogus__');
                $zend_auth->setPassword('__bogus__');
                $zend_auth->authenticate();
            } catch (\Exception $e) {
            }
            $raw_info = [];

            /** @var $ldap \Zend\Ldap\Ldap */
            $ldap = $zend_auth->getLdap();

            $dn = $ldap->getCanonicalAccountName($provided_dn, \Zend\Ldap\Ldap::ACCTNAME_FORM_DN);

            /** @var $rec \Zend\Ldap\Node */
            $rec = $ldap->getNode($dn);
            if ($rec) {
                $raw_info = array_merge($raw_info, $rec->getAttributes());

                if (!empty($raw_info['samaccountname'])) {
                    $raw_info['friendly_identity'] = Arrays::getFirstItem($raw_info['samaccountname']);
                } elseif (!empty($raw_info['uid'])) {
                    $raw_info['friendly_identity'] = Arrays::getFirstItem($raw_info['uid']);
                }
                if (!empty($raw_info['distinguishedname'])) {
                    $raw_info['identity'] = Arrays::getFirstItem($raw_info['distinguishedname']);
                } elseif (!empty($raw_info['dn'])) {
                    $raw_info['identity'] = Arrays::getFirstItem($raw_info['dn']);
                } else {
                    $raw_info['identity'] = $provided_dn;
                }

                if ($rec->getAttribute('givenName')) {
                    $raw_info['first_name'] = Arrays::getFirstItem($rec->getAttribute('givenName'));
                }
                if ($rec->getAttribute('SN')) {
                    $raw_info['last_name'] = Arrays::getFirstItem($rec->getAttribute('SN'));
                }

                if ($rec->getAttribute('givenName') && $rec->getAttribute('SN')) {
                    $raw_info['name'] = Arrays::getFirstItem($rec->getAttribute('givenName')).' '.Arrays::getFirstItem($rec->getAttribute('SN'));
                } elseif ($rec->getAttribute('name')) {
                    $raw_info['name'] = Arrays::getFirstItem($rec->getAttribute('name'));
                } elseif ($rec->getAttribute('CN')) {
                    $raw_info['name'] = Arrays::getFirstItem($rec->getAttribute('CN'));
                }

                if ($rec->getAttribute('mail')) {
                    $raw_info['email_address'] = Arrays::getFirstItem($rec->getAttribute('mail'));
                }

                if ($rec->getAttribute('jpegPhoto')) {
                    $raw_info['picture_data'] = Arrays::getFirstItem($rec->getAttribute('jpegPhoto'));
                } elseif ($rec->getAttribute('thumbnailPhoto')) {
                    $raw_info['picture_data'] = Arrays::getFirstItem($rec->getAttribute('thumbnailPhoto'));
                }
            }
        } catch (\Exception $e) {
            $raw_info['dp_error']          = 'Error when fetching node';
            $raw_info['exception_type']    = get_class($e);
            $raw_info['exception_message'] = $e->getMessage();
            $raw_info['exception_code']    = $e->getCode();
            $raw_info['exception_trace']   = SystemErrorHandler::formatBacktrace($e->getTrace());
        }

        $identity = new Identity($raw_info['identity'], $raw_info);

        return $identity;
    }

    /**
     * Search the AD for the user based on email address.
     */
    public function findRecordViaEmail()
    {
        if (!$this->set_username || !preg_match('#^.+@.+$#', $this->set_username)) {
            return;
        }

        if ($this->logger) {
            $this->logger->log('START Filter for email', Logger::DEBUG);
        }

        $zend_auth = $this->getZendAuthAdapter();
        // Bogus because zend only creates ldap obj when its needed,
        // so this is a hack to get it to set all the correct options
        // for us
        try {
            $zend_auth->setUsername('__bogus__');
            $zend_auth->setPassword('__bogus__');
            $zend_auth->authenticate();
        } catch (\Exception $e) {
        }

        /** @var $ldap \Zend\Ldap\Ldap */
        $ldap = $zend_auth->getLdap();

        $filter = sprintf('(&(objectClass=inetOrgPerson)('.$this->options[self::OPT_FIELD_EMAIL].'=%s))', \Zend\Ldap\Filter::escapeValue($this->set_username));
        if ($this->logger) {
            $this->logger->log("Sending filter: $filter", Logger::DEBUG);
        }

        try {
            $r = $ldap->search($filter, $this->options['baseDn']);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log('Failed to search: '.$e->getCode().' '.$e->getMessage(), Logger::DEBUG);
            }

            return;
        }

        if ($this->logger) {
            $this->logger->log('Filter results: '.print_r($r->toArray(), 1), Logger::DEBUG);
        }

        if ($r->count() == 1) {
            $arr = $r->getFirst();

            return $arr;
        }

        return;
    }

    public function findRecordViaDn($dn)
    {
        if ($this->logger) {
            $this->logger->log('START Filter for dn', Logger::DEBUG);
        }

        $zend_auth = $this->getZendAuthAdapter();
        // Bogus because zend only creates ldap obj when its needed,
        // so this is a hack to get it to set all the correct options
        // for us
        try {
            $zend_auth->setUsername('__bogus__');
            $zend_auth->setPassword('__bogus__');
            $zend_auth->authenticate();
        } catch (\Exception $e) {
        }

        /** @var $ldap \Zend\Ldap\Ldap */
        $ldap = $zend_auth->getLdap();

        return $ldap->getEntry($dn);
    }

    /**
     * Search the AD for the user based on username.
     */
    public function findRecordViaUsername()
    {
        if ($this->logger) {
            $this->logger->log('START Filter for username', Logger::DEBUG);
        }

        $zend_auth = $this->getZendAuthAdapter();
        // Bogus because zend only creates ldap obj when its needed,
        // so this is a hack to get it to set all the correct options
        // for us
        try {
            $zend_auth->setUsername('__bogus__');
            $zend_auth->setPassword('__bogus__');
            $zend_auth->authenticate();
        } catch (\Exception $e) {
        }

        /** @var $ldap \Zend\Ldap\Ldap */
        $ldap = $zend_auth->getLdap();

        $filter = sprintf('(&(objectClass=inetOrgPerson)('.$this->options[self::OPT_FIELD_USERNAME].'=%s))', \Zend\Ldap\Filter::escapeValue($this->set_username));
        if ($this->logger) {
            $this->logger->log("Sending filter: $filter", Logger::DEBUG);
        }

        try {
            $r = $ldap->search($filter, $this->options['baseDn']);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log('Failed to search: '.$e->getCode().' '.$e->getMessage(), Logger::DEBUG);
            }

            return;
        }

        if ($this->logger) {
            $this->logger->log('Filter results: '.print_r($r->toArray(), 1), Logger::DEBUG);
        }

        if ($r->count() == 1) {
            $arr = $r->getFirst();

            return $arr;
        }

        return;
    }

    /**
     * Check if SSL or TLS options enabled.
     */
    public function isSecure()
    {
        return (isset($this->options[self::OPT_SSL]) && $this->options[self::OPT_SSL])
                || (isset($this->options[self::OPT_TLS]) && $this->options[self::OPT_TLS]);
    }

    /**
     * @param \Orb\Log\Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return \Orb\Log\Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }
}

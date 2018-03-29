<?php

/**
 * Orb.
 *
 * @category Auth
 */

namespace Orb\Auth\Adapter;

use LightOpenID;
use Orb\Auth\Result;
use Orb\Auth\StateHandler\StateHandlerInterface;
use Orb\Util\Strings;
use Orb\Validator\StringEmail;

/**
 * Requirements:
 * - GoogleOpenID: http://andrewpeace.com/php-google-login-class.html.
 */
class Google extends AbstractCallbackAdatper implements DisplayContextInterface
{
    /** @var string */
    protected $display = 'page';

    /**
     * @var null|string
     */
    protected $apps_domain = null;

    /**
     * @param null|string $apps_domain Optionally limit to a specific google apps domain
     */
    public function __construct($apps_domain = null)
    {
        if ($apps_domain) {
            if (preg_match('#^http#', $apps_domain)) {
                $apps_domain = Strings::extractRegexMatch('#^https?://(.*?)/?.*?$#', $apps_domain);
            }

            $apps_domain = trim($apps_domain);
            $apps_domain = trim($apps_domain, '/');

            if (!$apps_domain) {
                $apps_domain = null;
            }
        }
        $this->apps_domain = $apps_domain;
    }

    /**
     * Sets the display context: page or popup.
     *
     * @param $context
     *
     * @throws \InvalidArgumentException
     */
    public function setDisplayContext($context)
    {
        $context = strtolower($context);
        if (!in_array($context, ['page', 'popup'])) {
            throw new \InvalidArgumentException("Invalid display context `$context`");
        }

        $this->display = $context;
    }

    /**
     * @return LightOpenID
     */
    private function getLightOpenId()
    {
        $return_url = $this->getCallbackUrl();
        $url_parts  = parse_url($return_url);

        $realm = $url_parts['scheme'].'://'.$url_parts['host'];
        if (!empty($url_parts['port'])) {
            $realm .= ':'.$url_parts['port'];
        }

        $openid            = new LightOpenID($url_parts['host']);
        $openid->realm     = $realm;
        $openid->returnUrl = $return_url;
        $openid->identity  = 'https://www.google.com/accounts/o8/id';
        $openid->required  = ['contact/email', 'namePerson/first', 'namePerson/last'];

        return $openid;
    }

    /**
     * Initialize the auth process by setting state, and returning a redirect result.
     *
     * @return \Orb\Auth\Result
     */
    protected function authenticateInitialize(StateHandlerInterface $state)
    {
        return new Result(Result::FAILURE);
        $openid       = $this->getLightOpenId();
        $redirect_url = $openid->authUrl();

        $params = [];
        if ($this->display == 'popup') {
            $params['openid.ui.mode'] = 'popup';
        }
        if ($this->apps_domain) {
            $params['hd'] = $this->apps_domain;
        }
        if ($params) {
            $redirect_url .= '&'.http_build_query($params);
        }

        $result = new Result(Result::REQUIRES_REDIRECT, null, [Result::MSG_REDIRECT => $redirect_url]);

        return $result;
    }

    /**
     * Process the callback and return a final result.
     *
     * @return \Orb\Auth\Result
     */
    protected function authenticateCallback(array $callback_data, StateHandlerInterface $state)
    {
        return new Result(Result::FAILURE);
        $openid = $this->getLightOpenId();

        if (!$openid->mode || $openid->mode == 'cancel') {
            return new Result(Result::FAILURE, null, ['error_code' => 'cancelled', 'error_message' => 'OpenID session cancelled']);
        }

        if (!$openid->validate()) {
            return new Result(Result::FAILURE, null, ['error_code' => 'not_valid', 'error_message' => 'OpenID session not valid']);
        }

        $user_id    = $openid->identity;
        $user_email = null;

        $attrs = $openid->getAttributes();
        if (!empty($attrs['contact/email'])) {
            $user_email = $attrs['contact/email'];
        }

        if ($user_id && $user_email && StringEmail::isValueValid($user_email)) {
            $raw = ['user_id' => $user_id, 'user_email' => $user_email];
            if (!empty($attrs['namePerson/first'])) {
                $raw['first_name'] = $attrs['namePerson/first'];
            }
            if (!empty($attrs['namePerson/last'])) {
                $raw['last_name'] = $attrs['namePerson/last'];
            }
            foreach ($attrs as $k => $v) {
                $k       = 'openid_'.$k;
                $raw[$k] = $v;
            }

            $identity = new \Orb\Auth\Identity($user_id, $raw);
            $identity->setFriendlyIdentity($user_email);
            $result = new Result(Result::SUCCESS, $identity);

            return $result;
        }

        return new Result(Result::FAILURE, null, ['error_code' => 'missing_data', 'error_message' => 'OpenID session did not return email address']);
    }
}

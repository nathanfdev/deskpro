<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Email\EmailAccount\OutgoingAccount;

use Application\DeskPRO\Email\EmailAccount\AccountConfigInterface;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidatorClassMetadata;

class Office365Config implements AccountConfigInterface
{
    const TYPE_PASSWORD = 'password';

    const TYPE_OAUTH = 'oauth';

    /**
     * @var string
     */
    public $user;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $domain;

    /**
     * @var string
     */
    public $client_id;

    /**
     * @var string
     */
    public $client_secret;

    /**
     * @var string
     */
    public $token;

    /**
     * @var string
     */
    public $refreshToken;

    /**
     * imap or oauth.
     */
    public $type;

    /**
     * {@inheritdoc}
     */
    public function serializeJsonArray()
    {
        return [
            'user'          => $this->user,
            'password'      => $this->password,
            'domain'        => $this->domain,
            'client_id'     => $this->client_id,
            'client_secret' => $this->client_secret,
            'token'         => $this->token,
            'refreshToken'  => $this->refreshToken,
            'type'          => $this->type,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function unserializeJsonArray(array $data)
    {
        $obj = new self();
        foreach ($data as $k => $v) {
            $obj->$k = $v;
        }

        return $obj;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'office365';
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * @return string
     */
    public function getClientSecret()
    {
        return $this->client_secret;
    }

    /**
     * @param string $client_id
     */
    public function setClientId($client_id)
    {
        $this->client_id = $client_id;
    }

    /**
     * @param string $client_secret
     */
    public function setClientSecret($client_secret)
    {
        $this->client_secret = $client_secret;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * @param string $refreshToken
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;
    }

    //###########################################################################
    // Validation Metadata
    //###########################################################################

    public static function loadValidatorMetadata(ValidatorClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('user', new Constraints\NotBlank());
        $metadata->addPropertyConstraint('domain', new Constraints\NotBlank());
        $metadata->addPropertyConstraint('client_id', new Constraints\NotBlank());
        $metadata->addPropertyConstraint('client_secret', new Constraints\NotBlank());
        $metadata->addPropertyConstraint('token', new Constraints\NotBlank());
        $metadata->addPropertyConstraint('refreshToken', new Constraints\NotBlank());
        $metadata->addPropertyConstraint('type', new Constraints\Choice([
            'choices' => [self::TYPE_PASSWORD, self::TYPE_OAUTH],
        ]));
    }
}

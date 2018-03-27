<?php

namespace DeskPRO\Bundle\AppStoreBundle\Oauth1;

use League\OAuth1\Client\Credentials\RsaClientCredentials as Oauth1RsaClientCredentials;

class RsaClientCredentials extends Oauth1RsaClientCredentials
{
    public function setRsaPrivateKeyFile($file)
    {
        return parent::setRsaPrivateKey($file);
    }

    public function setRsaPrivateKey($key)
    {
        $this->rsaPrivateKeyFile = null;
        $this->rsaPrivateKey     = $key;

        return $this;
    }

    public function __destruct()
    {
        if ($this->rsaPublicKey && is_resource($this->rsaPublicKey)) {
            openssl_free_key($this->rsaPublicKey);
        }

        if ($this->rsaPrivateKey && is_resource($this->rsaPrivateKey)) {
            openssl_free_key($this->rsaPrivateKey);
        }
    }
}

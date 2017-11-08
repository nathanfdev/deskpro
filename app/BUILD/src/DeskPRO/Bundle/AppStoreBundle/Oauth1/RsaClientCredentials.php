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

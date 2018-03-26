<?php

/**
 * Orb.
 *
 * @category Util
 */

namespace Orb\Util;

/**
 * Url utility functions.
 *
 * @static
 */
class Urls
{
    /**
     * Verifies if $email is from the $domain.
     *
     * example:
     * chris.tickner@gmail.com  and  deskpro.com  FALSE
     * chris.tickner@deskpro.com  and  deskpro.com  TRUE
     * chris.tickner@support.deskpro.com and deskpro.com FALSE
     * chris.tickner@support.deskpro.com and support.deskpro.com TRUE
     *
     * @param string $email  the email that we are checking vs the domain name
     * @param string $domain just a domain name
     *
     * @throws \InvalidArgumentException
     *
     * @return bool true if $domain is the extact domain used in the email of $email
     */
    public static function verifyEmailDomain($email, $domain)
    {
        if (preg_match('#^http#', $domain)) {
            $domain = Strings::extractRegexMatch('#^https?://(.*?)/?.*?$#', $domain);
        }

        $domain = trim($domain);
        $domain = trim($domain, '/');

        if (!$domain) {
            throw new \InvalidArgumentException('must provide a valid domain to Urls::verifyEmailDomain');
        }

        $email        = trim($email);
        $email_array  = explode('@', $email);
        $email_domain = array_pop($email_array);

        return $email_domain === $domain;
    }
}

<?php

/**
 * Orb.
 */

namespace Orb\Validator;

class StringEmail extends AbstractValidator implements StaticValidator
{
    const MAX_LEN            = 255;
    const OPT_REJECT_EXAMPLE = 'reject_example';

    /**
     * @param $value
     *
     * @return bool
     */
    public static function isValueValid($value)
    {
        $validator = new self();

        return $validator->isValid($value);
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public static function isExampleEmail($value)
    {
        if (preg_match('#@(.*?\.)?example\.(com|net|org)$#i', $value) || preg_match('#@(.*?\.)?(test|example|invalid)$#i', $value)) {
            return true;
        }

        return false;
    }

    /**
     * Check $value to see if its valid.
     *
     * @return bool
     */
    protected function checkIsValid($value)
    {
        if (!is_string($value)) {
            return false;
        }

        if (strpos($value, '@') === false || strlen($value) > self::MAX_LEN) {
            $this->addError('bad_email_format');

            return false;
        }

        list($name, $domain) = explode('@', $value, 2);
        $name                = trim($name);
        $domain              = trim($domain);

        if ($name === '' || !$domain) {
            $this->addError('empty_email');

            return false;
        }

        // Match the part before the @
        $regex_name = '#^[a-z0-9!\\#$%&\'*+/=?^_`{|}~-]+(?:\.[a-z0-9!\\#$%&\'*+/=?^_`{|}~-]+)*$#i';

        // Match the hostname after the @
        // Inspired by http://cpansearch.perl.org/src/ABIGAIL/Regexp-Common-2013031301/lib/Regexp/Common/URI/RFC2396.pm
        $regex_domain = '#^(?:(?:(?:(?:[a-zA-Z0-9][-a-zA-Z0-9]*)?[a-zA-Z0-9])[.])*(?:[a-zA-Z][-a-zA-Z0-9]*[a-zA-Z0-9]|[a-zA-Z]))$#i';

        // Match a IP address after the @
        $regex_ip = '#^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$#i';

        if (!preg_match($regex_name, $name)) {
            $this->addError('bad_email_name');

            return false;
        }

        if (!preg_match($regex_domain, $domain) and !preg_match($regex_ip, $domain)) {
            $this->addError('bad_email_domain');

            return false;
        }

        if ($this->getOption(self::OPT_REJECT_EXAMPLE, false)) {
            if (self::isExampleEmail($value)) {
                $this->addError('is_example_email');

                return false;
            }
        }

        return true;
    }
}

<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\Mail\RawMessage\Mail\Header;

use Zend\Mail\AddressList;
use Zend\Mail\Header\GenericHeader;
use Zend\Mail\Headers;

/**
 * This is a parser helper to fix bugs in Zend Mail's address list parser.
 *
 * We override classes that extend AbstractAddressList and register them as plugins in Rfc2822Decoder
 * so Zend Mail uses our classes instead of the defaults.
 *
 * Each of our custom classes extends the default (so types remain the same). Since we extend those default
 * base classes, we use this helper class to do the 'real' work of parsing.
 */
class AddressListParser
{
    public static function fromString($headerLine, $type, $header)
    {
        // split into name/value
        list($fieldName, $fieldValueRaw) = GenericHeader::splitHeaderLine($headerLine);

        $fieldValue = iconv_mime_decode($fieldValueRaw, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');

        if (strtolower($fieldName) !== $type) {
            throw new \Zend\Mail\Header\Exception\InvalidArgumentException(sprintf(
                'Invalid header line for "%s" string',
                __CLASS__
            ));
        }

        if ($fieldValue != $fieldValueRaw) {
            $header->setEncoding('UTF-8');
        }

        $fieldValue  = str_replace(Headers::FOLDING, ' ', $fieldValue);
        $addressList = $header->getAddressList();

        if (function_exists('imap_rfc822_parse_adrlist')) {
            self::populateAddressListViaImapParse($fieldValue, $addressList);
        } else {
            self::populateAddressListViaParse($fieldValue, $addressList);
        }

        return $header;
    }

    private static function populateAddressListViaParse($string, AddressList $al)
    {
        // This replaces quoted names with unique tokens.
        // We need this because the name might contain a ',' character, which
        // we explode on as an address separator
        $tokens = [];
        foreach ([
            '#"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"#s',
            "#'[^'\\\\]*(?:\\\\.[^'\\\\]*)*'#s",
        ] as $re) {
            $string = preg_replace_callback($re, function ($m) use (&$tokens) {
                $id = uniqid('t', true);

                $name = trim($m[0]);
                $name = trim($name, '"\'');
                $name = stripslashes($name);
                $tokens[$id] = $name;

                return $id;
            }, $string);
        }

        // Then this is the same as default AbstractAddressList::fromString
        // except we check the token map for the real name
        $values = explode(',', $string);
        foreach ($values as $addr_string) {
            $addr_string = trim($addr_string);

            $matches = null;
            if (!preg_match('/^((?P<name>.*?)<(?P<namedEmail>[^>]+)>|(?P<email>.+))$/', $addr_string, $matches)) {
                continue;
            }

            $name = null;
            if (isset($matches['name'])) {
                $name = trim($matches['name']);
            }
            if (empty($name)) {
                $name = null;
            }

            if ($name && isset($tokens[$name])) {
                $name = $tokens[$name];
            }

            if (isset($matches['namedEmail'])) {
                $email = $matches['namedEmail'];
            }
            if (isset($matches['email'])) {
                $email = $matches['email'];
            }
            $email = trim($email); // we may have leading whitespace

            // populate address list
            $al->add($email, $name);
        }
    }

    private static function populateAddressListViaImapParse($string, AddressList $al)
    {
        $list = imap_rfc822_parse_adrlist($string, 'localhost');

        foreach ($list as $addr) {
            $al->add($addr->mailbox.'@'.$addr->host, @$addr->personal ?: '');
        }
    }
}

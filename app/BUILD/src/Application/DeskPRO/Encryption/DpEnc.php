<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Encryption;

class DpEnc
{
    const DP_ENC_SIG_PREFIX     = 'dp_encrypted_string:::';
    const DP_ENC_SIG_PREFIX_LEN = 22;

    /**
     * @var bool
     */
    private $is_enabled;

    /**
     * @var string
     */
    private $key_file;

    /**
     * @var bool|string
     */
    private $key;

    /**
     * @param bool   $is_enabled
     * @param string $key_file
     */
    public function __construct($is_enabled, $key_file)
    {
        $this->key_file   = $key_file;
        $this->is_enabled = $is_enabled;
    }

    /**
     * Returns true if we want encryption ($is_enabled was passed to constructor).
     *
     * @return bool
     */
    public function isWanted()
    {
        return $this->is_enabled;
    }

    /**
     * Returns true if encryption is enabled ($is_enabled is on, and we actually have a real key).
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->is_enabled && $this->hasKey();
    }

    /**
     * Return true if we have a key.
     *
     * @return bool
     */
    public function hasKey()
    {
        return $this->getKey() !== null;
    }

    /**
     * Returns the key or null if there is no key.
     *
     * @return string|null
     */
    private function getKey()
    {
        if ($this->key === null) {
            $this->key = false;
            if (is_file($this->key_file) && is_readable($this->key_file)) {
                $key = @file_get_contents($this->key_file);
                if (!$key) {
                    $this->key = false;
                } else {
                    $this->key = base64_decode($key) ?: false;
                }
            }
        }

        return $this->key === false ? null : $this->key;
    }

    /**
     * @param string $string
     *
     * @throws \CannotPerformOperationException
     *
     * @return string
     */
    public function encrypt($string)
    {
        if (!$this->hasKey()) {
            throw new \RuntimeException('Cannot encrypt because no key');
        }

        return base64_encode(\Crypto::Encrypt($string, $this->getKey()));
    }

    /**
     * @param string $string
     *
     * @throws \CannotPerformOperationException
     * @throws \InvalidCiphertextException
     *
     * @return string
     */
    public function decrypt($string)
    {
        if (!$this->hasKey()) {
            throw new \InvalidCiphertextException('Cannot decrypt because no key');
        }

        return \Crypto::Decrypt(base64_decode($string), $this->getKey());
    }

    /**
     * Will encrypt the string and will encode it with special
     * signifiers that indicate it as an encrypted string. This is useful
     * for things like database strings that may or may not be encrypted.
     *
     * If encryption is NOT enabled, this will return the $string unchanged.
     * If encryption IS enabled, this will return an encrypted version of $string with the prefix signifier.
     *
     * @param string $string
     *
     * @return string
     */
    public function dpEncrypt($string)
    {
        if (!$this->isEnabled()) {
            return $string;
        }

        try {
            $enc_string = $this->encrypt($string);
        } catch (\Exception $e) {
            return $string;
        }

        return self::DP_ENC_SIG_PREFIX.$enc_string;
    }

    /**
     * Given a string that was encrypted via dpEncrypt, it will decrypt it.
     *
     * If the string is encrypted but we have no key, this may throw an exception.
     *
     * @param string $string
     *
     * @throws \CannotPerformOperationException
     * @throws \InvalidCiphertextException
     *
     * @return string
     */
    public function dpDecrypt($string)
    {
        if (substr($string, 0, self::DP_ENC_SIG_PREFIX_LEN) !== self::DP_ENC_SIG_PREFIX) {
            return $string;
        }

        $real_string = substr($string, self::DP_ENC_SIG_PREFIX_LEN);

        return $this->decrypt($real_string);
    }
}

<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Email\EmailAccount;

use Application\DeskPRO\Encryption\DpEnc;

/**
 * Class EmailAccountUtil
 * @package Application\DeskPRO\Email\EmailAccount
 */
class EmailAccountUtil
{
    /**
     * @param AccountConfigInterface $acc
     * @param DpEnc $enc
     *
     * @return AccountConfigInterface
     * @throws \CannotPerformOperationException
     * @throws \InvalidCiphertextException
     */
    public static function decryptIncomingAccount(AccountConfigInterface $acc, DpEnc $enc)
    {
        $new_acc = clone $acc;

        if (isset($acc->password)) {
            $new_acc->password = $enc->dpDecrypt($acc->password);
        }

        return $new_acc;
    }

    /**
     * @param AccountConfigInterface $acc
     * @param DpEnc $enc
     *
     * @return AccountConfigInterface
     * @throws \CannotPerformOperationException
     * @throws \InvalidCiphertextException
     */
    public static function decryptOutgoingAccount(AccountConfigInterface $acc, DpEnc $enc)
    {
        $new_acc = clone $acc;

        if (isset($acc->password)) {
            $new_acc->password = $enc->dpDecrypt($acc->password);
        }

        return $new_acc;
    }

    /**
     * @param AccountConfigInterface $acc
     * @param DpEnc                  $enc
     *
     * @return AccountConfigInterface
     */
    public static function encryptIncomingAccount(AccountConfigInterface $acc, DpEnc $enc)
    {
        $new_acc = clone $acc;

        if (isset($acc->password)) {
            $new_acc->password = $enc->dpEncrypt($acc->password);
        }

        return $new_acc;
    }

    /**
     * @param AccountConfigInterface $acc
     * @param DpEnc                  $enc
     *
     * @return AccountConfigInterface
     */
    public static function encryptOutgoingAccount(AccountConfigInterface $acc, DpEnc $enc)
    {
        $new_acc = clone $acc;

        if (isset($acc->password)) {
            $new_acc->password = $enc->dpEncrypt($acc->password);
        }

        return $new_acc;
    }
}

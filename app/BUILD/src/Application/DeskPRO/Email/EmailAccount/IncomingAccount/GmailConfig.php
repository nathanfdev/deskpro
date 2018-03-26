<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Email\EmailAccount\IncomingAccount;

use Application\DeskPRO\Email\EmailAccount\AccountConfigInterface;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidatorClassMetadata;

class GmailConfig implements AccountConfigInterface
{
    const TYPE_POP3 = 'pop3';

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
    public $clientId;

    /**
     * @var string
     */
    public $clientSecret;

    /**
     * @var string
     */
    public $token;

    /**
     * @var string
     */
    public $refreshToken;

    /**
     * 'read', 'delete', 'archive'.
     *
     * @var string
     */
    public $mode = 'read';

    /**
     * The mailbox to read from. Default blank means inbox.
     *
     * @var string
     */
    public $read_mailbox = null;

    /**
     * If using the 'archive' method, this is the mailbox name.
     *
     * @var string
     */
    public $archive_mailbox = null;

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
            'user'            => $this->user,
            'password'        => $this->password,
            'mode'            => $this->mode,
            'read_mailbox'    => $this->read_mailbox,
            'archive_mailbox' => $this->archive_mailbox,
            'type'            => $this->type,
            'token'           => $this->token,
            'refreshToken'    => $this->refreshToken,
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
        return 'gmail';
    }

    //###########################################################################
    // Validation Metadata
    //###########################################################################

    public static function loadValidatorMetadata(ValidatorClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('user', new Constraints\NotBlank());
        $metadata->addPropertyConstraint('token', new Constraints\NotBlank());
        $metadata->addPropertyConstraint('refreshToken', new Constraints\NotBlank());
        $metadata->addPropertyConstraint('mode', new Constraints\Choice([
            'choices' => ['read', 'delete', 'archive'],
        ]));
        $metadata->addPropertyConstraint('type', new Constraints\Choice([
            'choices' => [self::TYPE_POP3, self::TYPE_OAUTH],
        ]));
    }
}

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

class ImapConfig implements AccountConfigInterface
{
    /**
     * @var string
     */
    public $host;

    /**
     * Pop3 default is 143, secure 993.
     *
     * @var int
     */
    public $port = 143;

    /**
     * @var string
     */
    public $user;

    /**
     * @var string
     */
    public $password;

    /**
     * 'ssl' or 'tls'.
     *
     * @var null|string
     */
    public $secure_mode = null;

    /**
     * disable certificate validation (validate by default).
     *
     * @var bool
     */
    public $no_validation = false;

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
     * {@inheritdoc}
     */
    public function serializeJsonArray()
    {
        return [
            'host'            => $this->host,
            'port'            => $this->port,
            'user'            => $this->user,
            'password'        => $this->password,
            'secure_mode'     => $this->secure_mode,
            'no_validation'   => $this->no_validation,
            'mode'            => $this->mode,
            'read_mailbox'    => $this->read_mailbox,
            'archive_mailbox' => $this->archive_mailbox,
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
        return 'imap';
    }

    //###########################################################################
    // Validation Metadata
    //###########################################################################

    public static function loadValidatorMetadata(ValidatorClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('host', new Constraints\NotBlank());
        $metadata->addPropertyConstraint('port', new Constraints\GreaterThan(['value' => 1]));
        $metadata->addPropertyConstraint('secure_mode', new Constraints\Choice([
            'choices' => ['none', 'ssl', 'tls'],
        ]));
        $metadata->addPropertyConstraint('mode', new Constraints\Choice([
            'choices' => ['read', 'delete', 'archive'],
        ]));
    }
}

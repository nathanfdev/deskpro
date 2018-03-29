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

class Pop3Config implements AccountConfigInterface
{
    /**
     * @var string
     */
    public $host;

    /**
     * Pop3 default is 110, secure 995.
     *
     * @var int
     */
    public $port = 110;

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
     * @var bool
     */
    public $disable_cert_validation = false;

    /**
     * {@inheritdoc}
     */
    public function serializeJsonArray()
    {
        return [
            'host'                    => $this->host,
            'port'                    => $this->port,
            'user'                    => $this->user,
            'password'                => $this->password,
            'secure_mode'             => $this->secure_mode,
            'disable_cert_validation' => $this->disable_cert_validation,
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
        return 'pop3';
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
    }
}

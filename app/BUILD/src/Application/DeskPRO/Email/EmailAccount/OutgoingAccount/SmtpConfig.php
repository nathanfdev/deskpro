<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Email\EmailAccount\OutgoingAccount;

use Application\DeskPRO\Email\EmailAccount\AccountConfigInterface;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidatorClassMetadata;

class SmtpConfig implements AccountConfigInterface
{
    /**
     * @var string
     */
    public $host;

    /**
     * Default SMTP port is 25, secure 465.
     *
     * @var int
     */
    public $port = 25;

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
     * @var string
     */
    public $helo_string = '';

    /**
     * @var bool
     */
    private $disable_cert_validation = false;

    /**
     * @return bool
     */
    public function isDisableCertValidation()
    {
        return $this->disable_cert_validation;
    }

    /**
     * @param bool $disable_cert_validation
     */
    public function setDisableCertValidation($disable_cert_validation)
    {
        $this->disable_cert_validation = $disable_cert_validation;
    }

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
            'helo_string'             => $this->helo_string,
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
        return 'smtp';
    }

    //###########################################################################
    // Validation Metadata
    //###########################################################################

    public static function loadValidatorMetadata(ValidatorClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('host', new Constraints\NotBlank());
        $metadata->addPropertyConstraint('port', new Constraints\GreaterThan(['value' => 1]));
        $metadata->addPropertyConstraint(
            'secure_mode',
            new Constraints\Choice(
                [
                    'choices' => ['none', 'ssl', 'tls'],
                ]
            )
        );
    }
}

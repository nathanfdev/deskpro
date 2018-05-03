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

class SendmailConfig implements AccountConfigInterface
{
    /**
     * @var string
     */
    public $sendmail_path;

    /**
     * {@inheritdoc}
     */
    public function serializeJsonArray()
    {
        return [
            'sendmail_path' => $this->sendmail_path,
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
        return 'sendmail';
    }

    //###########################################################################
    // Validation Metadata
    //###########################################################################

    public static function loadValidatorMetadata(ValidatorClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('sendmail_path', new Constraints\NotBlank());
    }
}

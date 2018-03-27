<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Ban an email address.
 *
 * @property string $banned_email
 * @property bool $is_pattern
 */
class BanEmail extends DomainObject
{
    /**
     * The banned email address.
     *
     * @var string
     */
    protected $banned_email;

    /**
     * True if this is a pattern rather than a specific address.
     *
     * @var bool
     */
    protected $is_pattern = false;

    /**
     * @return BanEmail
     */
    public static function createEmailBan()
    {
        return new self();
    }

    public function getId()
    {
        return $this->banned_email;
    }

    /**
     * @param string $email
     */
    public function setBannedEmail($email)
    {
        if (strpos($email, '*') !== false) {
            $this['is_pattern'] = true;
        }

        $this->setModelField('banned_email', $email);
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\BanEmail';
        $metadata->setPrimaryTable(['name' => 'ban_emails']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField(
            [
                'fieldName'  => 'banned_email',
                'type'       => 'string',
                'length'     => 255,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'banned_email',
                'id'         => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'is_pattern',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'is_pattern',
            ]
        );
    }
}

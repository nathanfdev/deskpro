<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Ban an email address
 *
 * @property string $banned_email
 * @property boolean $is_pattern
 */
class BanEmail extends DomainObject
{
    /**
     * The banned email address
     *
     * @var string
     */

    protected $banned_email;

    /**
     * True if this is a pattern rather than a specific address
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

    ############################################################################
    # Doctrine Metadata
    ############################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\BanEmail';
        $metadata->setPrimaryTable(array('name' => 'ban_emails'));
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField(
            array(
                 'fieldName'  => 'banned_email',
                 'type'       => 'string',
                 'length'     => 255,
                 'precision'  => 0,
                 'scale'      => 0,
                 'nullable'   => false,
                 'columnName' => 'banned_email',
                 'id'         => true,
            )
        );
        $metadata->mapField(
            array(
                 'fieldName'  => 'is_pattern',
                 'type'       => 'boolean',
                 'precision'  => 0,
                 'scale'      => 0,
                 'nullable'   => false,
                 'columnName' => 'is_pattern',
            )
        );
    }
}

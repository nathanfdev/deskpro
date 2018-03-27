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
use Orb\Util\Arrays;
use Orb\Util\Strings;

/**
 * @property int $id
 * @property Organization $add_organization
 * @property Usergroup $add_usergroup
 * @property array $email_patterns
 * @property int $run_order
 */
class UserRule extends DomainObject
{
    /**
     * The unique ID.
     *
     * @var int
     */
    protected $id = null;

    /**
     * An array of email address patterns.
     *
     * @var array
     */
    protected $email_patterns = [];

    /**
     * @var \Application\DeskPRO\Entity\Organization
     */
    protected $add_organization;

    /**
     * @var \Application\DeskPRO\Entity\Usergroup
     */
    protected $add_usergroup;

    /**
     * The order in which to run this source.
     *
     * @var int
     */
    protected $run_order = 0;

    /**
     * @return UserRule
     */
    public static function createUserRule()
    {
        return new self();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the patterns string which is a number of patterns separated by a newline.
     *
     * @param $patterns
     */
    public function setPatternsString($patterns)
    {
        $items = [];

        $patterns = Strings::standardEol($patterns);
        $patterns = explode("\n", $patterns);
        foreach ($patterns as $p) {
            $p       = Strings::utf8_strtolower($p);
            $items[] = trim($p);
        }

        $items = Arrays::removeFalsey($items);

        $this->setModelField('email_patterns', $items);
    }

    /**
     * Get the patterns string.
     *
     * @return string
     */
    public function getPatternsString()
    {
        return implode("\n", $this->email_patterns);
    }

    /**
     * Check if an email address to see if it matches any of the patterns in this rule.
     *
     * @param string $email_address
     *
     * @return string
     */
    public function isEmailMatch($email_address)
    {
        $email_address = Strings::utf8_strtolower($email_address);

        $patterns = $this->email_patterns;

        if (!is_array($patterns)) {
            $patterns = explode("\n", $this->email_patterns);
        }

        foreach ($patterns as $pattern) {
            if (Strings::isStarMatch($pattern, $email_address)) {
                return true;
            }
        }

        return false;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\UserRule';
        $metadata->setPrimaryTable(['name' => 'user_rules']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField(
            [
                'fieldName'  => 'id',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'id',
                'id'         => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'email_patterns',
                'type'       => 'array',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'email_patterns',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'run_order',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'run_order',
            ]
        );
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'add_organization',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Organization',
                'cascade'      => [
                    0 => 'remove',
                    1 => 'persist',
                    3 => 'merge',
                ],
                'mappedBy'    => null,
                'inversedBy'  => null,
                'joinColumns' => [
                    0 => [
                        'name'                 => 'add_organization_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'add_usergroup',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Usergroup',
                'cascade'      => [
                    'persist',
                    'merge',
                ],
                'mappedBy'    => null,
                'inversedBy'  => null,
                'joinColumns' => [
                    0 => [
                        'name'                 => 'add_usergroup_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
    }
}

<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Orb\Util\Strings;

/**
 * Templates used in the system.
 */
class Phrase extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * The unique ID.
     *
     * @var int
     */
    protected $id = null;

    /**
     * The language this phrase belongs to.
     *
     * @var Language
     */
    protected $language;

    /**
     * The name of the phrase.
     *
     * @var string
     */
    protected $name = null;

    /**
     * Phrases can belong to groups. The group is the string
     * before the first dot in the name. deskpro.profile, the group is 'deskpro'.
     *
     * @var string
     */
    protected $groupname;

    /**
     * @var string
     */
    protected $phrase;

    /**
     * @var string
     */
    protected $original_phrase = '';

    /**
     * @var string
     */
    protected $original_hash = '';

    /**
     * Is this phrase marked as outdated?
     *
     * This happens when we detect the original hash stored is different from what
     * is on the filesystem.
     *
     * @var bool
     */
    protected $is_outdated = false;

    /**
     * True if the phrase is 'managed' by the system and is not a custom user phrase.
     *
     * @var bool
     */
    protected $is_managed = false;

    /**
     * @var \DateTime
     */
    protected $created_at;

    /**
     * @var \DateTime
     */
    protected $updated_at;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->setModelField('created_at', $this->updated_at = new \DateTime());
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param Language $language
     *
     * @return Phrase
     */
    public function setLanguage($language)
    {
        $this->setModelField('language', $language);

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->setModelField('name', $name);

        $groupName = self::getGroupFromName($name);

        if ($groupName) {
            $this->setModelField('groupname', $groupName);
        }
    }

    /**
     * @return string
     */
    public function getGroupname()
    {
        return $this->groupname;
    }

    /**
     * SetName should be used instead.
     *
     * @param string $groupName
     *
     * @return Phrase
     */
    public function setGroupname($groupName)
    {
        $this->setModelField('groupname', $groupName);

        return $this;
    }

    /**
     * @return string
     */
    public function getPhrase()
    {
        return $this->phrase;
    }

    /**
     * @param string $phrase
     *
     * @return Phrase
     */
    public function setPhrase($phrase)
    {
        $this->setModelField('phrase', $phrase);

        return $this;
    }

    /**
     * @return string
     */
    public function getOriginalPhrase()
    {
        return $this->original_phrase;
    }

    /**
     * @param string $originalPhrase
     */
    public function setOriginalPhrase($originalPhrase)
    {
        $this->setModelField('original_phrase', $originalPhrase);
    }

    /**
     * @return string
     */
    public function getOriginalHash()
    {
        return $this->original_hash;
    }

    /**
     * @param string $originalHash
     */
    public function setOriginalHash($originalHash)
    {
        $this->setModelField('original_hash', $originalHash);
    }

    /**
     * @return bool
     */
    public function isManaged()
    {
        return $this->is_managed;
    }

    /**
     * @param bool $isManaged
     */
    public function setIsManaged($isManaged)
    {
        $this->setModelField('is_managed', $isManaged);
    }

    public function incUpdatedAt()
    {
        $this->setModelField('updated_at', new \DateTime());
    }

    public function __toString()
    {
        return $this->phrase;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public static function getGroupFromName($name)
    {
        $groupname = Strings::rexplode('.', $name, 2);
        $groupname = array_shift($groupname);

        return $groupname;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Phrase';
        $metadata->setPrimaryTable([
            'name'    => 'phrases',
            'indexes' => [
                'name_idx' => [
                    'columns' => [
                        'groupname',
                        'name',
                    ],
                ],
            ],
        ]);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->addLifecycleCallback('incUpdatedAt', 'preUpdate');
        $metadata->mapField([
            'fieldName'  => 'id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'id',
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'name',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'name',
        ]);
        $metadata->mapField([
            'fieldName'  => 'groupname',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'groupname',
        ]);
        $metadata->mapField([
            'fieldName'  => 'phrase',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'phrase',
        ]);
        $metadata->mapField([
            'fieldName'  => 'original_phrase',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'original_phrase',
        ]);
        $metadata->mapField([
            'fieldName'  => 'original_hash',
            'type'       => 'string',
            'length'     => 40,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'original_hash',
        ]);
        $metadata->mapField([
            'fieldName'  => 'is_outdated',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'is_outdated',
        ]);
        $metadata->mapField([
            'fieldName'  => 'is_managed',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'is_managed',
            'options'    => ['default' => '0'],
        ]);
        $metadata->mapField([
            'fieldName'  => 'created_at',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'created_at',
        ]);
        $metadata->mapField([
            'fieldName'  => 'updated_at',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'updated_at',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne([
            'fieldName'    => 'language',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Language',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                0 => [
                    'name'                 => 'language_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
            'dpApi' => true,
        ]);
    }
}

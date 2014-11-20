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
 * @subpackage ApiBundle
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Orb\Util\Strings;

/**
 * @property int $id
 * @property string $code
 * @property string note
 * @property string $keyString
 * @property Person $person
 * @property array $flags
 */

class ApiKey extends DomainObject
{
    const FLAG_ADMIN_MANAGE = 'admin_manage';
    const FLAG_SUPER_KEY    = 'super';

    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * A note or description about the key (ie what its used for).
     *
     * @var string
     */
    protected $note = '';

    /**
     * @var array
     */
    protected $flags = array();

    /**
     * @var ArrayCollection
     */
    protected $logs;

    public function __construct()
    {
        $this->regenerateApiKey();
        $this->logs = new ArrayCollection();
    }

    /**
     * Regenerate the API key
     */
    public function regenerateApiKey()
    {
        $this['code'] = Strings::random(25, Strings::CHARS_KEY);
    }

    /**
     * Get a "key string". This is a combined ID and code like id:code
     * that is used in auth lookup.
     *
     * @return string
     */
    public function getKeyString()
    {
        return $this->id.':'.$this->code;
    }

    /**
     * @param  string $flag
     * @return bool
     */
    public function isFlagSet($flag)
    {
        return in_array($flag, $this->flags);
    }

    /**
     * @param  bool  $primary
     * @param  bool  $deep
     * @param  array $visited
     * @return array
     */
    public function toApiData($primary = true, $deep = true, array $visited = array())
    {
        $data              = parent::toApiData($primary, false, $visited);
        $data['keyString'] = $this->getKeyString();
        $data['person']    = $this->person ? $this->person['id'] : null;
        foreach ($this->flags as $f) {
            $data[$f] = true;
        }

        return $data;
    }

    ############################################################################
    # Doctrine Metadata
    ############################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->inheritanceType           = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
        $metadata->customRepositoryClassName = 'Application\\DeskPRO\\EntityRepository\\ApiKey';
        $metadata->changeTrackingPolicy      = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
        $metadata->generatorType             = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;

        $metadata->setPrimaryTable(array(
            'name' => 'api_keys',
        ));

        $metadata->mapField(array(
            'columnName' => 'id',
            'fieldName'  => 'id',
            'type'       => 'integer',
            'id'         => true,
            'nullable'   => false,
        ));
        $metadata->mapField(array(
            'columnName' => 'code',
            'fieldName'  => 'code',
            'type'       => 'string',
            'length'     => 25,
            'nullable'   => false,
        ));
        $metadata->mapField(array(
            'columnName' => 'note',
            'fieldName'  => 'note',
            'type'       => 'text',
            'nullable'   => false,
        ));
        $metadata->mapField(array(
            'columnName' => 'flags',
            'fieldName'  => 'flags',
            'type'       => 'simple_array',
            'nullable'   => true,
        ));

        $metadata->mapManyToOne(array(
            'fieldName'    => 'person',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => array(array(
                'name'                 => 'person_id',
                'referencedColumnName' => 'id',
                'nullable'             => true,
                'onDelete'             => 'cascade',
                'columnDefinition'     => null,
            )),
        ));

        $metadata->mapOneToMany(array(
            'fieldName'    => 'logs',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\ApiKeyLog',
            'mappedBy'     => 'key',
            'inversedBy'   => null,
            'orderBy'      => array('id' => 'DESC'),
            'cascade'                    => array('persist', 'remove'), // doesn't work
        ));
    }
}

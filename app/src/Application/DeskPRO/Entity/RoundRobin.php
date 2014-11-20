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

use Application\DeskPRO\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Article
 */
class RoundRobin extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * Next agent in queue
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $next = null;

    /**
     * Agents
     *
     * @var ArrayCollection
     */
    protected $agents;

    /**
     * @var string
     */
    protected $title;

    public function __construct()
    {
        $this->agents = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function toApiData($primary = true, $deep = true, array $visited = array())
    {
        foreach ($this->agents as $agentRef) {
            $a = $agentRef->agent;
            if (!$a['is_agent'] || $a['is_disabled'] || $a['is_deleted']) {
                $this->agents->removeElement($agentRef);
            }
        }
        $data = parent::toApiData($primary, $deep, $visited);

        return $data;
    }

    ############################################################################
    # Doctrine Metadata
    ############################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->inheritanceType           = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
        $metadata->generatorType             = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;
        $metadata->changeTrackingPolicy      = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
        $metadata->customRepositoryClassName = 'Application\\DeskPRO\\EntityRepository\\RoundRobin';
        $metadata->setPrimaryTable(array('name' => 'round_robin'));

        $metadata->mapField(array(
            'columnName' => 'id',
            'fieldName'  => 'id',
            'type'       => 'integer',
            'nullable'   => false,
            'id'         => true,
        ));
        $metadata->mapField(array(
            'fieldName'  => 'title',
            'columnName' => 'title',
            'type'       => 'string',
            'length'     => 255,
            'nullable'   => false,
        ));

        $metadata->mapManyToOne(array(
            'fieldName'    => 'next',
            'dpApi'        => true,
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
            'joinColumns'  => array(array(
                'name'                 => 'next_agent_id',
                'referencedColumnName' => 'id',
                'nullable'             => true,
                'onDelete'             => 'set null',
                'columnDefinition'     => NULL,
            )),
        ));

        $metadata->mapOneToMany(array(
            'fieldName'     => 'agents',
            'dpApi'         => true,
            'dpApiDeep'     => true,
            'targetEntity'  => 'Application\\DeskPRO\\Entity\\RoundRobinAgent',
            'mappedBy'      => 'robin',
            'orphanRemoval' => true,
            'orderBy'       => array('sort' => 'ASC'),
        ));
    }
}

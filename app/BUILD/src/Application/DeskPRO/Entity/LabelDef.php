<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;

/**
 * These are pre-defined labels that are allowed to be used.
 *
 * @property string $label_type
 * @property string $label
 * @property int $total
 * @JMS\ExclusionPolicy("all")
 */
class LabelDef extends DomainObject
{
    const DEFAULT_COLOR = '#cccccc';

    const TYPE_TASKS     = 'task';
    const TYPE_TICKETS   = 'tickets';
    const TYPE_PEOPLE    = 'people';
    const TYPE_ORGS      = 'organizations';
    const TYPE_NEWS      = 'news';
    const TYPE_FEEDBACK  = 'feedback';
    const TYPE_DOWNLOADS = 'downloads';
    const TYPE_CHATS     = 'chat_conversations';
    const TYPE_ARTICLES  = 'articles';

    /**
     * Label type.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $label_type;

    /**
     * Label itself.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $label;

    /**
     * RGB color representation.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string css color
     */
    protected $color = '';

    /**
     * Label times used total counter.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $total = 0;

    public function __construct(array $data = array())
    {
        foreach ($data as $k => $v) {
            if (property_exists($this, $k)) {
                $this[$k] = trim($v);
            }
        }
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->setModelField('label', $label);
    }

    /**
     * Get the name of the entity used to store label associations for this type.
     *
     * @return string
     */
    public function getLabelEntityName()
    {
        return App::getEntityRepository('DeskPRO:LabelDef')->getLabelEntityFromType($this->label_type);
    }

    /**
     * Get the table name used to store label associations for this type.
     *
     * @return string
     */
    public function getLabelTable()
    {
        $ent   = App::getEntityRepository('DeskPRO:LabelDef')->getLabelEntityFromType($this->label_type);
        $class = App::getEntityClass($ent);
        $table = $class::getTableName();

        return $table;
    }

    /**
     * @param string $c
     */
    public function setColor($c)
    {
        if (!trim($c)) {
            $this->setModelField('color', '');
        } else {
            $c = trim($c);
            if ($c === '' || $c === '#') {
                $this->setModelField('color', '');
            } else {
                $this->setModelField('color', $c);
            }
        }
    }

    /**
     * Increase LabelDef total by 1.
     */
    public function increment()
    {
        ++$this->total;
        $this->_onPropertyChanged('total', null, $this->total);
    }

    /**
     * Decrease LabelDef total by 1.
     */
    public function decrement()
    {
        --$this->total;
        $this->_onPropertyChanged('total', null, $this->total);
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    ############################################################################
    # Doctrine Metadata
    ############################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\LabelDef';
        $metadata->setPrimaryTable(
            [
                'name'    => 'label_defs',
                'indexes' => [
                    'type_total_idx' => [
                        'columns' => [
                            'label_type',
                            'total',
                        ],
                    ],
                ],
            ]
        );
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField(
            [
                'fieldName'  => 'label_type',
                'type'       => 'string',
                'length'     => 50,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'label_type',
                'id'         => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'label',
                'type'       => 'string',
                'length'     => 255,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'label',
                'id'         => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'color',
                'type'       => 'string',
                'nullable'   => false,
                'columnName' => 'color',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'total',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'total',
            ]
        );
    }
}

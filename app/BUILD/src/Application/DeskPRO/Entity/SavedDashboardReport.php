<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * This is just a tab, that holds a collection of widgets.
 *
 * @property int    $id
 * @property string $title
 */
class SavedDashboardReport extends DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var string it is a tab title
     */
    protected $title = '';

    /**
     * @var ArrayCollection
     */
    protected $saved_widgets;

    /**
     * @var array
     */
    protected $variables;

    /**
     * @var string
     */
    protected $authcode;

    /**
     * @var \DateTime
     */
    protected $date_created;

    public function __construct()
    {
        $this->saved_widgets = new ArrayCollection();
        $this->date_created  = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return SavedDashboardWidget[]|ArrayCollection
     */
    public function getSavedWidgets()
    {
        return $this->saved_widgets;
    }

    /**
     * @param SavedDashboardWidget $savedWidget
     *
     * @return $this
     */
    public function addSavedWidget(SavedDashboardWidget $savedWidget)
    {
        $this->saved_widgets->add($savedWidget);

        return $this;
    }

    /**
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * @param array $variables
     *
     * @return $this
     */
    public function setVariables(array $variables)
    {
        $this->variables = $variables;

        return $this;
    }

    /**
     * @return string
     */
    public function getAuthcode()
    {
        return $this->authcode;
    }

    /**
     * @param string $authcode
     *
     * @return $this
     */
    public function setAuthcode($authcode)
    {
        $this->authcode = $authcode;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @param \DateTime $dateCreated
     *
     * @return $this
     */
    public function setDateCreated(\DateTime $dateCreated)
    {
        $this->date_created = $dateCreated;

        return $this;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    /**
     * @param ClassMetadata $metadata
     *
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setPrimaryTable(
            [
                'name' => 'saved_dashboard_report',
            ]
        );
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_DEFERRED_IMPLICIT);
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
                'fieldName'  => 'title',
                'type'       => 'string',
                'length'     => 255,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'title',
            ]
        );

        $metadata->mapField(
            [
                'fieldName'  => 'variables',
                'type'       => 'json_array',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'variables',
            ]
        );

        $metadata->mapField(
            [
                'fieldName'  => 'date_created',
                'type'       => 'datetime',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'date_created',
            ]
        );

        $metadata->mapField(
            [
                'fieldName'  => 'authcode',
                'type'       => 'string',
                'precision'  => 0,
                'scale'      => 0,
                'length'     => 100,
                'nullable'   => true,
                'columnName' => 'authcode',
            ]
        );

        $metadata->mapOneToMany([
            'fieldName'    => 'saved_widgets',
            'targetEntity' => SavedDashboardWidget::class,
            'mappedBy'     => 'saved_report',
            'inversedBy'   => null,
            'orderBy'      => ['position' => 'ASC'],
            'cascade'      => ['persist', 'remove'],
        ]);

        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
    }
}

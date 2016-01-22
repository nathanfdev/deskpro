<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
 */
namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use Doctrine\ORM\Mapping\ClassMetadata;
use Orb\Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

/**
 * @property int $id
 * @property string $name
 * @property string $theme_id
 * @property Blob $logo_blob
 */
class Brand extends DomainObject
{
    /**
     * The unique ID.
     *
     * @var int
     */
    protected $id = null;

    /**
     * @var string the brand name
     */
    protected $name;

    /**
     * @var \DeskPRO\Bundle\AppBundle\Entity\ThemeSet
     */
    protected $theme_set;

    /**
     * @var \DeskPRO\Bundle\AppBundle\Entity\ThemeSet
     */
    protected $edit_theme_set;

    /**
     * @var Blob
     */
    protected $logo_blob;

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ThemeSet
     */
    public function getThemeSet()
    {
        return $this->theme_set;
    }

    /**
     * @param ThemeSet $theme_set
     */
    public function setThemeSet(ThemeSet $theme_set)
    {
        $this->setModelField('theme_set', $theme_set);
    }

    /**
     * @return ThemeSet
     */
    public function getEditThemeSet()
    {
        return $this->edit_theme_set;
    }

    /**
     * @param ThemeSet $theme_set
     */
    public function setEditThemeSet(ThemeSet $theme_set)
    {
        $this->setModelField('edit_theme_set', $theme_set);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->setModelField('name', $name);
    }

    public function toApiData($primary = true, $deep = true, array $visited = array())
    {
        $data              = parent::toApiData($primary, $deep, $visited);
        $data['logo_blob'] = $this->logo_blob ? $this->logo_blob->toApiData() : null;

        return $data;
    }

    ############################################################################
    # Doctrine Metadata
    ############################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->setCustomRepositoryClass('Application\DeskPRO\EntityRepository\Brand');
        $builder->setChangeTrackingPolicyNotify();
        $builder->setTable('brands');

        $builder->mapId();
        $builder->mapString('name');
        $builder->createOneToOne('theme_set', 'DeskPRO\Bundle\AppBundle\Entity\ThemeSet')->cascadePersist()->build();
        $builder->createOneToOne('edit_theme_set', 'DeskPRO\Bundle\AppBundle\Entity\ThemeSet')->build();
        $builder->createOneToOne('logo_blob', 'Application\DeskPRO\Entity\Blob')->addJoinColumn('logo_blob_id', 'id', true, false, 'cascade');
    }
}

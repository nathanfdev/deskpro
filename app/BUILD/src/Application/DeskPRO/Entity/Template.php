<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Templates used in the system.
 *
 * @property int       $id
 * @property string    $name
 * @property string    $template_code
 * @property string    $template_compiled
 * @property Brand     $brand
 * @property ThemeSet  $theme_set
 * @property \DateTime $date_created
 * @property \DateTime $date_updated
 */
class Template extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * The unique ID.
     *
     * @var int
     */
    protected $id = null;

    /**
     * The logical name of the template. E.g., UserBundle:Main:resources.html.twig.
     *
     * @var string
     */
    protected $name;

    /**
     * The raw template.
     *
     * @var string
     */
    protected $template_code = '';

    /**
     * The template compiled to PHP.
     *
     * @var string
     */
    protected $template_compiled = '';

    /**
     * @var ThemeSet
     */
    protected $theme_set;

    /**
     * @var \DateTime
     */
    protected $date_created;

    /**
     * @var \DateTime
     */
    protected $date_updated;

    public function __construct()
    {
        $this->setModelField('date_created', new \DateTime());
        $this->setModelField('date_updated', new \DateTime());
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTemplateCode()
    {
        return $this->template_code;
    }

    public function getTemplateCompiled()
    {
        return $this->template_compiled;
    }

    public function setThemeSet(ThemeSet $themeSet)
    {
        $this->setModelField('theme_set', $themeSet);

        return $this;
    }

    public function setTemplate($code, $compiled)
    {
        $this->setModelField('template_code', $code);
        $this->setModelField('template_compiled', $compiled);
        $this->setModelField('date_updated', new \DateTime());
    }

    public function isCustom()
    {
        return strpos($this->name, ':custom_') !== false;
    }

    public function getBaseName()
    {
        $parts = explode(':', $this->name);
        $name  = array_pop($parts);
        $name  = str_replace('.html.twig', '', $name);

        return $name;
    }

    /**
     * @return ThemeSet
     */
    public function getThemeSet()
    {
        return $this->theme_set;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Template';
        $metadata->setPrimaryTable(['name' => 'templates']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
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
            'fieldName'  => 'template_code',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'template_code',
        ]);
        $metadata->mapField([
            'fieldName'  => 'template_compiled',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'template_compiled',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_created',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'date_created',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_updated',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'date_updated',
        ]);

        $builder = new ClassMetadataBuilder($metadata);
        $builder->createManyToOne('theme_set', 'DeskPRO\Bundle\AppBundle\Entity\ThemeSet')->build();

        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
    }
}

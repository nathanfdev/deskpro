<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\Domain\ObjectTranslatable;
use DeskPRO\Bundle\AppBundle\Entity\ObjectTranslatableInterface;
use DeskPRO\Bundle\AppBundle\Entity\ObjectTranslatableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TextSnippetCategory.
 *
 * @method string getTitle()
 * @method setTitle()
 */
class TextSnippetCategory extends \Application\DeskPRO\Domain\DomainObject implements ObjectTranslatableInterface
{
    use ObjectTranslatableTrait;

    const TYPE_TICKET = 'tickets';
    const TYPE_CHAT   = 'chat';

    /**
     * @var int
     */
    protected $id = null;

    /**
     * The type of snippets this cat contains.
     *
     * @var string
     */
    protected $typename;

    /**
     * Who created the cat.
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person = null;

    /**
     * Everyone can see it?
     *
     * @var bool
     */
    protected $is_global = false;

    /**
     * @var ArrayCollection
     *
     * @Assert\Count(min=1)
     * @Assert\Valid()
     */
    protected $props_translations;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->getObjectTranslatable();
        $this->props_translations = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getPermType()
    {
        return $this->is_global ? 'global' : 'me';
    }

    /**
     * @return string
     */
    public function getTypename()
    {
        return $this->typename;
    }

    /**
     * @param string $typename
     *
     * @return $this
     */
    public function setTypename($typename)
    {
        $this->setModelField('typename', $typename);

        return $this;
    }

    /**
     * @param Person $person
     *
     * @return $this
     */
    public function setPerson(Person $person = null)
    {
        $this->setModelField('person', $person);

        return $this;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param $bool
     *
     * @return $this
     */
    public function setIsGlobal($bool)
    {
        $this->setModelField('is_global', $bool);

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsGlobal()
    {
        return $this->is_global;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|ObjectLang[]
     */
    public function getTitleTranslations()
    {
        return $this->getObjectPropTranslations('title');
    }

    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        $data          = parent::toApiData($primary, $deep, $visited);
        $data['title'] = [];

        foreach (App::getContainer()->getLanguageData()->getAll() as $lang) {
            $title           = $this->getObjectTranslatable()->getObjectProp('title', $lang);
            $data['title'][] = [
                'language_id' => $lang->getId(),
                'locale'      => $lang->getLocale(),
                'value'       => $title,
            ];
        }

        return $data;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    /**
     * @deprecated use $props_translations instead
     */
    public function getObjectTranslatable()
    {
        return ObjectTranslatable::loadObjectTranslatable($this);
    }

    /**
     * @deprecated use $props_translations instead
     */
    public static function loadObjectTranslatableMetadata()
    {
        return ['fields' => ['title']];
    }

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TextSnippetCategory';
        $metadata->setPrimaryTable(['name' => 'text_snippet_categories']);
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
                'fieldName'  => 'typename',
                'type'       => 'string',
                'length'     => 30,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'typename',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'is_global',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'is_global',
            ]
        );
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'person',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'person_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );

        ObjectTranslatable::loadEntityMetadata($metadata);
    }
}

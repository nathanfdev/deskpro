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
use DeskPRO\Bundle\AppBundle\Entity\TextSnippetContent;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TextSnippet.
 */
class TextSnippet extends \Application\DeskPRO\Domain\DomainObject implements ObjectTranslatableInterface
{
    use ObjectTranslatableTrait;

    /**
     * @var int
     */
    protected $id = null;

    /**
     * Who created the snippet.
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person = null;

    /**
     * @var \Application\DeskPRO\Entity\TextSnippetCategory
     *
     * @Assert\NotNull()
     */
    protected $category;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    protected $shortcut_code = '';

    /**
     * @var bool
     */
    protected $is_draft = false;

    /**
     * @var ArrayCollection|ObjectLang[]
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
     * @param TextSnippetCategory $category
     *
     * @return $this
     */
    public function setCategory(TextSnippetCategory $category = null)
    {
        $this->setModelField('category', $category);

        return $this;
    }

    /**
     * @return TextSnippetCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string $sc
     *
     * @return $this
     */
    public function setShortcutCode($sc)
    {
        if (!$sc) {
            $this->setModelField('shortcut_code', '');
        } else {
            $this->setModelField('shortcut_code', $sc);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getShortcutCode()
    {
        return $this->shortcut_code;
    }

    /**
     * @param bool $is_draft
     *
     * @return $this
     */
    public function setIsDraft($is_draft)
    {
        $this->setModelField('is_draft', $is_draft);

        return $this;
    }

    /**
     * @return bool
     */
    public function isDraft()
    {
        return $this->is_draft;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     *
     * @Assert\Count(min=1)
     * @Assert\Valid()
     */
    public function getTitleTranslations()
    {
        return $this->getObjectPropTranslations('title');
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     *
     * @Assert\Count(min=1)
     * @Assert\Valid()
     */
    public function getSnippetTranslations()
    {
        return $this->getObjectPropTranslations('snippet');
    }

    /**
     * @return TextSnippetContent[]
     */
    public function getTextSnippetContents()
    {
        $result = [];
        foreach ($this->props_translations as $translation) {
            $language = $translation->getLanguage();
            if (!isset($result[$language->getLocale()])) {
                $result[$language->getLocale()] = new TextSnippetContent($language);
            }

            /** @var TextSnippetContent $content */
            $content = $result[$language->getLocale()];
            if ($translation->getPropName() === 'snippet') {
                $content->setContent($translation->getValue());
            } else {
                $content->setTitle($translation->getValue());
            }
        }

        return $result;
    }

    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        $data                = parent::toApiData($primary, $deep, $visited);
        $data['category_id'] = $this->category ? $this->category->getId() : 0;
        $data['title']       = [];
        $data['snippet']     = [];
        $data['is_draft']    = $this->is_draft;

        foreach (App::getContainer()->getLanguageData()->getAll() as $lang) {
            $title   = $this->getObjectTranslatable()->getObjectProp('title', $lang);
            $snippet = $this->getObjectTranslatable()->getObjectProp('snippet', $lang);

            $data['title'][]   = ['language_id' => $lang->getId(), 'locale' => $lang->getLocale(), 'value' => $title];
            $data['snippet'][] = ['language_id' => $lang->getId(), 'locale' => $lang->getLocale(), 'value' => $snippet];
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
        return ['fields' => ['title', 'snippet']];
    }

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TextSnippet';
        $metadata->setPrimaryTable(['name' => 'text_snippets']);
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
            'fieldName'  => 'shortcut_code',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'shortcut_code',
        ]);
        $metadata->mapField(
            [
                'fieldName'  => 'is_draft',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'is_draft',
            ]
        );
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne([
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
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'category',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\TextSnippetCategory',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                0 => [
                    'name'                 => 'category_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);

        ObjectTranslatable::loadEntityMetadata($metadata);
    }
}

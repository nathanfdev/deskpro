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
     * @var ArrayCollection
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
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
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
     */
    public function setShortcutCode($sc)
    {
        if (!$sc) {
            $this->setModelField('shortcut_code', '');
        } else {
            $this->setModelField('shortcut_code', $sc);
        }
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

    public function toApiData($primary = true, $deep = true, array $visited = array())
    {
        $data                = parent::toApiData($primary, $deep, $visited);
        $data['category_id'] = $this->category ? $this->category->getId() : 0;
        $data['title']       = array();
        $data['snippet']     = array();
        $data['is_draft']    = $this->is_draft;

        foreach (App::getContainer()->getLanguageData()->getAll() as $lang) {
            $title   = $this->getObjectTranslatable()->getObjectProp('title', $lang);
            $snippet = $this->getObjectTranslatable()->getObjectProp('snippet', $lang);

            $data['title'][]   = array('language_id' => $lang->getId(), 'locale' => $lang->getLocale(), 'value' => $title);
            $data['snippet'][] = array('language_id' => $lang->getId(), 'locale' => $lang->getLocale(), 'value' => $snippet);
        }

        return $data;
    }

    ############################################################################
    # Doctrine Metadata
    ############################################################################

    public function getObjectTranslatable()
    {
        return ObjectTranslatable::loadObjectTranslatable($this);
    }

    public static function loadObjectTranslatableMetadata()
    {
        return array('fields' => array('title', 'snippet'));
    }

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TextSnippet';
        $metadata->setPrimaryTable(array('name' => 'text_snippets'));
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField(array('fieldName' => 'id', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'id', 'id' => true));
        $metadata->mapField(array('fieldName' => 'shortcut_code', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'shortcut_code'));
        $metadata->mapField(
            array(
                'fieldName'  => 'is_draft',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'is_draft',
            )
        );
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne(array('fieldName' => 'person', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Person', 'mappedBy' => null, 'inversedBy' => null, 'joinColumns' => array(0 => array('name' => 'person_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => 'set null', 'columnDefinition' => null))));
        $metadata->mapManyToOne(array('fieldName' => 'category', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TextSnippetCategory', 'mappedBy' => null, 'inversedBy' => null, 'joinColumns' => array(0 => array('name' => 'category_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => 'cascade', 'columnDefinition' => null))));

        ObjectTranslatable::loadEntityMetadata($metadata);
    }
}

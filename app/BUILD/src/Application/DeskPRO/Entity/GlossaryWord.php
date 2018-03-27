<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\EntityRepository\GlossaryWord as GlossaryWordRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Glossary.
 *
 * @JMS\ExclusionPolicy("all")
 */
class GlossaryWord extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * The unique word ID.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id = null;

    /**
     * The word itself.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     * @Assert\NotBlank()
     */
    protected $word;

    /**
     * This word definition string representation.
     *
     * @JMS\Expose()
     * @JMS\Type("to_string<Application\DeskPRO\Entity\GlossaryWordDefinition>")
     *
     * @Assert\NotNull()
     *
     * @var GlossaryWordDefinition
     */
    protected $definition;

    /**
     * Brand linked to the glossary word.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Brand>")
     *
     * @var Brand
     */
    protected $brand;

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
    public function getWord()
    {
        return $this->word;
    }

    /**
     * @param string $word
     *
     * @return GlossaryWord
     */
    public function setWord($word)
    {
        $this->setModelField('word', $word);

        return $this;
    }

    /**
     * @return GlossaryWordDefinition
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * @param GlossaryWordDefinition $definition
     *
     * @return $this
     */
    public function setDefinition($definition)
    {
        $this->setModelField('definition', $definition);

        return $this;
    }

    /**
     * @return Brand
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param Brand $brand
     *
     * @return GlossaryWord
     */
    public function setBrand($brand)
    {
        $this->setModelField('brand', $brand);

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getWord();
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = GlossaryWordRepository::class;
        $metadata->setPrimaryTable(
            [
                'name'              => 'glossary_words',
                'uniqueConstraints' => [
                    'prop_ref' => [
                        'columns' => [
                            'word',
                            'brand_id',
                        ],
                    ],
                ],
            ]
        );
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
                'fieldName'  => 'word',
                'type'       => 'string',
                'length'     => 255,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'word',
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'definition',
                'targetEntity' => GlossaryWordDefinition::class,
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'definition_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => false,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'brand',
                'targetEntity' => Brand::class,
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    [
                        'name'                 => 'brand_id',
                        'referencedColumnName' => 'id',
                        'onDelete'             => 'set null',
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
    }
}

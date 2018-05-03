<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Glossary.
 *
 * @JMS\ExclusionPolicy("all")
 */
class GlossaryWordDefinition extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * The unique id.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id = null;

    /**
     * Word definition itself.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     * @Assert\NotBlank
     */
    protected $definition;

    /**
     * An array of words belongs this definition.
     *
     * @JMS\Expose()
     * @JMS\Type("collection<to_string<Application\DeskPRO\Entity\GlossaryWord>>")
     *
     * @var ArrayCollection
     */
    protected $words;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * GlossaryWordDefinition constructor.
     */
    public function __construct()
    {
        $this->words = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->definition;
    }

    /**
     * @return string
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * @param string $definition
     */
    public function setDefinition($definition)
    {
        $this->setModelField('definition', $definition);
    }

    /**
     * @param array|\ArrayAccess $words
     */
    public function setWords($words)
    {
        /** @var GlossaryWord[] $words */
        if (is_array($words) || $words instanceof \ArrayAccess) {
            foreach ($words as $word) {
                $this->words->add($word);
                $word->setDefinition($this);
            }
        }
    }

    /**
     * @param GlossaryWord $word
     */
    public function addWord(GlossaryWord $word)
    {
        $this->words->add($word);
        $word->setDefinition($this);
    }

    /**
     * @param string $word
     * @param Brand  $brand
     *
     * @return GlossaryWord|void
     */
    public function addNewWord($word, $brand)
    {
        $word = trim(strval($word));
        if ($word === '') {
            return;
        }

        $existing = App::getEntityRepository(GlossaryWord::class)->findBy(['word' => $word, 'brand' => $brand]);
        if ($existing) {
            return;
        }
        foreach ($this->words as $existing_word) {
            if (strtolower($word) == strtolower($existing_word->word)) {
                return;
            }
        }

        $obj = new GlossaryWord();
        $obj->setWord($word);
        $obj->setDefinition($this);
        $obj->setBrand($brand);

        $this->words->add($obj);

        return $obj;
    }

    /**
     * @param array $words
     * @param Brand $brand
     */
    public function updateWords(array $words, $brand)
    {
        if (!$words) {
            throw new \InvalidArgumentException('Must provide some words');
        }

        $words_test = array_map('strtolower', $words);

        foreach ($this->words as $existing_key => $existing_word) {
            $key = array_search(strtolower($existing_word->word), $words_test);
            if ($key !== false) {
                unset($words_test[$key]);
            } else {
                $this->words->remove($existing_key);
            }
        }

        foreach (array_keys($words_test) as $key) {
            $this->addNewWord($words[$key], $brand);
        }
    }

    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        $data          = parent::toApiData($primary, $deep, $visited);
        $data['words'] = [];
        foreach ($this->words as $word) {
            $data['words'][$word->id] = $word->word;
        }

        return $data;
    }

    /**
     * @return ArrayCollection
     */
    public function getWords()
    {
        return $this->words;
    }

    /**
     * An array of words belongs this definition.
     *
     * @return array
     */
    public function getStringWords()
    {
        return array_map(function (GlossaryWord $word) {
            return $word->getWord();
        }, $this->getWords()->toArray());
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Basic';
        $metadata->setPrimaryTable(['name' => 'glossary_word_definitions']);
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
                'fieldName'  => 'definition',
                'type'       => 'text',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'definition',
            ]
        );
        $metadata->mapOneToMany(
            [
                'fieldName'    => 'words',
                'targetEntity' => GlossaryWord::class,
                'cascade'      => [
                    0 => 'remove',
                    1 => 'persist',
                    3 => 'merge',
                ],
                'mappedBy'      => 'definition',
                'orphanRemoval' => true,
            ]
        );
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
    }
}

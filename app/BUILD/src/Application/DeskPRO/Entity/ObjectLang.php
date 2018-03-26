<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ObjectLang.
 *
 * @JMS\ExclusionPolicy("ALL")
 */
class ObjectLang extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    protected $ref;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    protected $ref_type;

    /**
     * Note: Don't validate this property because it will be set up in the doctrine lifecycle callback.
     *
     * @var string
     */
    protected $ref_id;

    /**
     * @var Language
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Language>")
     *
     * @Assert\NotNull()
     */
    protected $language;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    protected $prop_name;

    /**
     * @var string
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     */
    protected $value = '';

    /**
     * @var object
     */
    protected $_set_object;

    /**
     * Create a new lang object.
     *
     * @param Language|int $lang      The lang ID of a lang or the lang itself
     * @param object       $object    The domain object to set the lang for. This is any object that has getObjectRef
     * @param string       $prop_name The property ID of the thing we are translating
     * @param string       $value     The value ID of the thing we are translating
     *
     * @throws \InvalidArgumentException
     *
     * @return \Application\DeskPRO\Entity\ObjectLang
     */
    public static function createObjectLang($lang, $object, $prop_name, $value)
    {
        if ($lang === 0 || $lang === null || $lang === 'default') {
            $lang = App::getContainer()->getDataService('language')->getDefault();
        } elseif (!is_object($lang)) {
            $lang = App::getContainer()->getDataService('language')->get($lang);
        }

        if (!$lang || !($lang instanceof Language)) {
            throw new \InvalidArgumentException('Invalid language');
        }

        $ol = new self();
        if (is_object($object)) {
            $ol->setObject($object);
        } else {
            $ol->setRef($object);
        }

        if ($value === null || $value === false) {
            $value = '';
        }

        $ol->setPropName($prop_name);
        $ol->setValue($value);
        $ol->setLanguage($lang);

        return $ol;
    }

    public function setLanguage(Language $lang = null)
    {
        $this->setModelField('language', $lang);

        return $this;
    }

    public function setValue($value)
    {
        $this->setModelField('value', $value);

        return $this;
    }

    /**
     * @param object $object
     *
     * @return $this
     */
    public function setObject($object)
    {
        $this->_set_object = $object;
        $this->setRef($object->getObjectRef());

        return $this;
    }

    /**
     * @param string $ref
     *
     * @return $this
     */
    public function setRef($ref)
    {
        $this->setModelField('ref', $ref);

        if (strpos($ref, '.') !== false) {
            list($type, $id) = explode('.', $ref, 2);
            $this->setModelField('ref_type', $type);
            $this->setModelField('ref_id', $id);
        } else {
            $this->setModelField('ref_type', null);
            $this->setModelField('ref_id', null);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * @param string $prop_name
     *
     * @return $this
     */
    public function setPropName($prop_name)
    {
        $this->setModelField('prop_name', strtolower($prop_name));

        return $this;
    }

    /**
     * @return string
     */
    public function getPropName()
    {
        return $this->prop_name;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return Language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @return string
     */
    public function getRefType()
    {
        return $this->ref_type;
    }

    /**
     * @return string
     */
    public function getRefId()
    {
        return $this->ref_id;
    }

    public function _resetRefCode()
    {
        if ($this->_set_object) {
            $this->setModelField('ref', $this->_set_object->getObjectRef());
            $this->_set_object = null;
        }
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\ObjectLang';
        $metadata->setPrimaryTable(
            [
                'name'    => 'object_lang',
                'indexes' => [
                    'prop_ref_type' => [
                        'columns' => [
                            'ref_type',
                            'ref_id',
                        ],
                    ],
                ],
                'uniqueConstraints' => [
                    'prop_ref' => [
                        'columns' => [
                            'ref',
                            'prop_name',
                            'language_id',
                        ],
                    ],
                ],
            ]
        );
        $metadata->addLifecycleCallback('_resetRefCode', 'prePersist');
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
                'fieldName'  => 'ref',
                'type'       => 'string',
                'length'     => 200,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'ref',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'ref_type',
                'type'       => 'string',
                'length'     => 100,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'ref_type',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'ref_id',
                'type'       => 'integer',
                'nullable'   => true,
                'columnName' => 'ref_id',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'prop_name',
                'type'       => 'string',
                'length'     => 100,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'prop_name',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'value',
                'type'       => 'text',
                'nullable'   => false,
                'columnName' => 'value',
            ]
        );
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'language',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Language',
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'language_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
    }
}

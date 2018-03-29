<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * A log of API requests per key.
 */
class ApiKeyLog extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var \Application\DeskPRO\Entity\ApiKey
     */
    protected $key;

    /**
     * @var array
     */
    protected $request;

    /**
     * @var array
     */
    protected $response;

    /**
     * @var int
     */
    protected $time;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this['time']     = time();
        $this['request']  = [];
        $this['response'] = [];
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ApiKey
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param ApiKey $key
     *
     * @return $this
     */
    public function setKey(ApiKey $key = null)
    {
        $this->setModelField('key', $key);

        return $this;
    }

    /**
     * @return array
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param array $request
     *
     * @return $this
     */
    public function setRequest(array $request = null)
    {
        $this->setModelField('request', $request);

        return $this;
    }

    /**
     * @return array
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param array $response
     *
     * @return $this
     */
    public function setResponse(array $response = null)
    {
        $this->setModelField('response', $response);

        return $this;
    }

    /**
     * @return int
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param int $time
     *
     * @return $this
     */
    public function setTime($time)
    {
        $this->setModelField('time', $time);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        $data         = parent::toApiData($primary, $deep, $visited);
        $data['time'] = date('Y-m-d H:i:s', $data['time']);

        return $data;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->generatorType             = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\ApiKeyLog';

        $metadata->setPrimaryTable([
            'name' => 'api_key_log',
        ]);

        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);

        $metadata->mapField([
            'fieldName'  => 'id',
            'type'       => 'integer',
            'nullable'   => false,
            'columnName' => 'id',
            'id'         => true,
        ]);

        $metadata->mapField([
            'fieldName'  => 'time',
            'type'       => 'integer',
            'nullable'   => false,
            'columnName' => 'time',
        ]);

        $metadata->mapField([
            'fieldName'  => 'request',
            'type'       => 'array',
            'nullable'   => false,
            'columnName' => 'request',
        ]);

        $metadata->mapField([
            'fieldName'  => 'response',
            'type'       => 'array',
            'nullable'   => false,
            'columnName' => 'response',
        ]);

        $metadata->mapManyToOne([
            'fieldName'    => 'key',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\ApiKey',
            'mappedBy'     => null,
            'inversedBy'   => 'logs',
            'joinColumns'  => [
                0 => [
                    'name'                 => 'key_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => false,
                    'onDelete'             => 'cascade',
                ],
            ],
        ]);

        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
    }
}

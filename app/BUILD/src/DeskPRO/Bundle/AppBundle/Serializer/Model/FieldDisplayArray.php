<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model;

use Application\DeskPRO\CustomFields\FieldDisplayArray as FieldDisplayArrayObject;
use JMS\Serializer\Annotation as JMS;

class FieldDisplayArray
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $elId;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $hasValue;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $id;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $name;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $title;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $value;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $fieldHandler;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $handlerClass;

    public function __construct(FieldDisplayArrayObject $fieldDisplayArray)
    {
        $fieldDisplayArray->initValue('handler');
        $data = $fieldDisplayArray->toArray();

        $this->elId         = $data['elId'];
        $this->hasValue     = $data['hasValue'];
        $this->id           = $data['id'];
        $this->name         = $data['name'];
        $this->title        = $data['title'];
        $this->value        = $data['value'];
        $this->fieldHandler = $data['field_handler'];
        $this->handlerClass = get_class($data['handler']);
    }
}

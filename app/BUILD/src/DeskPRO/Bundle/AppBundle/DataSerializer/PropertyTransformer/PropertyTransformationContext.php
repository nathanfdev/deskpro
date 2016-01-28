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

namespace DeskPRO\Bundle\AppBundle\DataSerializer\PropertyTransformer;

use DeskPRO\Bundle\AppBundle\DataSerializer\DataSerializerContext;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * PropertyTransformerInterface's use this as input, and it allows us to encapsulate data access and resolving.
 *
 * Example usage:
 *
 * $context = new PropertyTransformationContext($ticket_entity, 'participants');
 *
 * Now, inside of a PropertyTransformer:
 *
 * $context->getValue() // see if you can transform the value, if not just return and do nothing
 *
 * However, if you can transform the value, you call:
 *
 * $context->transform('new value');
 */
class PropertyTransformationContext
{
    /**
     * @var mixed the piece of data that has the property we are concerned with
     */
    protected $data;

    /**
     * @var string the property name on we are concerned with
     */
    protected $property_name;

    /**
     * @var mixed internally stored/cached value (we only access it once from)
     */
    protected $value;

    /**
     * @var bool wether we already populated or not
     */
    protected $found_value;

    /**
     * @var mixed the final transformation of the property
     */
    protected $transformed_value;

    /**
     * @var bool weather we actually did a transformation or not yet
     */
    protected $is_transformed;

    /**
     * @var DataSerializerContext the main serialization context
     */
    private $serializer_context;

    public function __construct($data, $property_name, DataSerializerContext $serializer_context)
    {
        $this->property_name      = $property_name;
        $this->data               = $data;
        $this->is_transformed     = false;
        $this->found_value        = false;
        $this->serializer_context = $serializer_context;
    }

    public function transform($transformed_value)
    {
        $this->transformed_value = $transformed_value;
        $this->is_transformed    = true;
    }

    public function isTransformed()
    {
        return $this->is_transformed;
    }

    /**
     * @return mixed
     */
    public function getTransformedValue()
    {
        return $this->transformed_value;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return mixed
     */
    public function getPropertyName()
    {
        return $this->property_name;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        if (!$this->found_value) {
            $accessor          = PropertyAccess::createPropertyAccessor();
            $this->value       = $accessor->getValue($this->data, $this->property_name);
            $this->found_value = true;
        }

        return $this->value;
    }

    /**
     * @return DataSerializerContext
     */
    public function getSerializerContext()
    {
        return $this->serializer_context;
    }
}

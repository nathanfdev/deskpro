<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\ApiBundle\DataSerializer\PropertyTransformer;

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
    protected $data;
    protected $property_name;
    protected $value;
    protected $found_value;
    protected $transformed_value;
    protected $is_transformed;

    public function __construct($data, $property_name)
    {
        $this->property_name = $property_name;
        $this->data = $data;
        $this->is_transformed = false;
        $this->found_value = false;
    }

    public function transform($transformed_value)
    {
        $this->transformed_value = $transformed_value;
        $this->is_transformed = true;
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
            $accessor = PropertyAccess::createPropertyAccessor();
            $this->value = $accessor->getValue($this->data, $this->property_name);
            $this->found_value = true;
        }

        return $this->value;
    }
}

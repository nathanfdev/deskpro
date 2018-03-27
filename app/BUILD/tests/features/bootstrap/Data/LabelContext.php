<?php

namespace DpBehat\Data;

use DpBehat\BaseContext;

/**
 * Class LabelContext.
 */
class LabelContext extends BaseContext
{
    /**
     * @Given  no labels with type :type exist
     *
     * @param $type
     */
    public function removeLabelDefinitionsWithType($type)
    {
        $this->em()->getConnection()->delete('label_defs', ['label_type' => $type]);
    }
}

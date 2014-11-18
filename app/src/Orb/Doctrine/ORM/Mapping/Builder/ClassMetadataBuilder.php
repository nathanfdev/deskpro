<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 * Orb
 *
 * @package Orb
 * @subpackage Doctrine
 */

namespace Orb\Doctrine\ORM\Mapping\Builder;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder as BaseBuilder;

class ClassMetadataBuilder extends BaseBuilder
{
    public function mapId($fieldName = 'id', $type = 'AUTO')
    {
        $this->createField($fieldName, 'integer')->generatedValue($type)->isPrimaryKey()->build();
    }


    public function mapString($fieldName, $length = 256, $nullable = true, $unique = false)
    {
        $this->addField(
            $fieldName, 'string', array('nullable' => $nullable, 'length' => $length, 'unique' => $unique)
        );
    }


    public function mapText($fieldName, $nullable = true)
    {
        $this->addField($fieldName, 'text', array('nullable' => $nullable));
    }


    public function mapBoolean($fieldName)
    {
        $this->addField($fieldName, 'boolean');
    }


    public function mapInteger($fieldName, $nullable = true, $precision = 0, $scale = 0)
    {
        $this->addField(
            $fieldName,
            'integer',
            array(
                'precision' => $precision,
                'scale'     => $scale,
                'nullable'  => $nullable,
            )
        );
    }


    public function mapDateTime($fieldName, $nullable = true)
    {
        $this->addField(
            $fieldName, 'datetime', array('nullable' => $nullable)
        );
    }
}

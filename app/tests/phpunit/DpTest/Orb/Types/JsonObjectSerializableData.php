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

namespace DpTest\Orb\Types;

use Orb\Types\JsonObjectSerializable;

/**
 * TestObject.
 */
class JsonObjectSerializableData implements JsonObjectSerializable
{
    public $a;
    public $b;
    public $c;
    protected $_y;
    private $_z;

    public function __construct($a, $b, $c, $y = null, $z = null)
    {
        $this->a  = $a;
        $this->b  = $b;
        $this->c  = $c;
        $this->_y = $y;
        $this->_z = $z;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return implode(',', $this->serializeJsonArray());
    }

    /**
     * {@inheritdoc}
     */
    public function serializeJsonArray()
    {
        return array(
            'a'  => $this->a,
            'b'  => $this->b,
            'c'  => $this->b,
            '_y' => $this->_y,
            '_z' => $this->_z,
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function unserializeJsonArray(array $data)
    {
        $obj = new self(
            $data['a'],
            $data['b'],
            $data['c'],
            $data['_y'],
            $data['_z']
        );

        return $obj;
    }
}

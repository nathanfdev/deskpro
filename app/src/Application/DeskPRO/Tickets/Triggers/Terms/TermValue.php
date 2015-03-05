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
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Triggers\Terms;

class TermValue
{
    /**
     * @var mixed
     */
    private $value = null;

    /**
     * @var callback
     */
    private $value_callback;

    /**
     * @param  mixed     $value
     * @return TermValue
     */
    public static function createWithValue($value)
    {
        return new self($value, null);
    }

    /**
     * @param  callback  $value_callback
     * @return TermValue
     */
    public static function createWithCallback($value_callback)
    {
        return new self(null, $value_callback);
    }

    /**
     * @param mixed    $value
     * @param callback $value_callback
     */
    private function __construct($value = null, $value_callback = null)
    {
        $this->value = $value;
        $this->value_callback = $value_callback;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        if ($this->value_callback) {
            return call_user_func($this->value_callback);
        }

        return $this->value;
    }
}

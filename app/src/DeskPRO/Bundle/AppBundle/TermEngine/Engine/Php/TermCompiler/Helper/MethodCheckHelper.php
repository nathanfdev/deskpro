<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at https://www.deskpro.com/eula/                            |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\Helper;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpression;
use DeskPRO\Bundle\AppBundle\TermEngine\TermCompilerHelperInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Orb\Util\Arrays;

class MethodCheckHelper implements TermCompilerHelperInterface
{
    public function getId()
    {
        return 'method_check';
    }

    /**
     * @param $method_call
     * @param $op
     * @param array $input
     * @return PhpCheck
     */
    public function checkContains($method_call, $op, array $input)
    {
        $array = $this->filterInput($input);

        $check = '';
        $check .= '$check = ';
        if (strtolower($op) == strtolower(TermInterface::OP_NOT)) {
            $check .= '!';
        }
        $check .= 'in_array(' . $method_call . ', ';
        $check .= '\Orb\Util\Arrays::flatten(array(';
        $check .= implode(',', $array);
        $check .= ')));';

        return new PhpCheck(
            $check
        );
    }

    /**
     * @param $method_call
     * @param $op
     * @param $input
     * @param bool $check_identical
     * @return \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck
     */
    public function checkEquality($method_call, $op, $input, $check_identical = false)
    {
        // we turn it into an array to use the same filtering logic as the contains check
        $array = $this->filterInput(array($input));
        $input = current($array);

        $check = '';
        $check .= '$check = (';
        $check .= $method_call;
        $check .= ' ';
        if (strtolower($op) == strtolower(TermInterface::OP_NOT)) {
            $check .= '!=';
        } else {
            $check .= '==';
        }
        if ($check_identical) {
            $check .= '=';
        }
        $check .= ' ';
        $check .= $input;
        $check .= ');';

        return new PhpCheck(
            $check
        );
    }

    /**
     * @param array $input
     * @return array
     */
    protected function filterInput(array $input)
    {
        $array = array();
        foreach ($input as $in) {
            if (is_string($in)) {
                $array[] = "'" . addslashes($in) . "'";
            } elseif ($in instanceof TermEngineExpression) {
                $array[] = '$this->evaluateExpression(\'' . addslashes((string)$in) . '\')';
            } else {
                $array[] = $in;
            }
        }

        return Arrays::flatten($array); // always flatten
    }
}

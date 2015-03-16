<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
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
define('DP_ROOT', __DIR__ . '/../../../../..');
define('DP_WEB_ROOT', __DIR__ . '/../../../../../..');
define('DP_CONFIG_FILE', __DIR__ . '/../../../../../config.php');
define('DP_INTERFACE', 'user');

require_once DP_ROOT . '/sys/preboot.php';
require_once DP_ROOT . '/sys/autoload.php';

////
// POC OUTPUT
////

use Application\DeskPRO\Entity\Ticket;

class Checker_dlaj4
{
    protected $context;

    public function __construct($context)
    {
        $this->context = $context;
    }

    public function isCheck(Ticket $ticket)
    {

        return
            ((
                    ($dep = $ticket->getDepartment()) && (in_array($dep->getId(), array(2, 3)))
                ) || (
                    ($agent = $ticket->getAgent()) && (in_array($agent->getId(), array(5, 1, 12)))
                ));

    }
}

////
// END POC OUTPUT
////

$ticket = new Ticket();
$dep = new \Application\DeskPRO\Entity\Department();
$dep->id = 2;

$ticket->setDepartment($dep);

$chek = new Checker_dlaj4(array());
var_dump($chek->isCheck($ticket));
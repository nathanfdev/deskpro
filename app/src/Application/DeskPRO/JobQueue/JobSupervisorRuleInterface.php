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
 * @subpackage JobQueue
 */

namespace Application\DeskPRO\JobQueue;

/**
 * The JobSupervisor has a collection of these rules, and every time it runs asks each rule to
 * check if it is violated or not. If it is, it throws a JobSupervisorViolationException with the message
 * of what was violated. After catching the exception in check() JobSupervisor will then ask the
 * rule to attempt to fix the violation via a call to attemptToFix(), which is an optional method
 * because many rule violations cannot be fixed automatically.
 */
interface JobSupervisorRuleInterface
{
    /**
     * Checks the business logic behind this rule. Returns null if all is well. If a rule is violated, it
     * should throw the JobSupervisorException with a detailed message of the problem it found.
     *
     * @return null
     * @throws JobSupervisorException
     */
    public function check();

    /**
     * Only called if a JobSupervisorException is thrown in check(), which is always called first. This method
     * MAY attempt to fix the problem. It is optional, however, and any rule that cannot be fixed should just
     * have this method return false.
     *
     * @return bool true on successful fix, false on unsuccessful fix
     */
    public function attemptToFix();
}

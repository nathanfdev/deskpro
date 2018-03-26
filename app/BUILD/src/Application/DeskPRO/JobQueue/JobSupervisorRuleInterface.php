<?php

/**
 * DeskPRO.
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
